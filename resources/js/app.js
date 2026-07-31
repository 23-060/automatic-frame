import JSZip from 'jszip';
import QRCode from 'qrcode';

document.addEventListener('DOMContentLoaded', () => {
    // --- DOM Elements ---
    const tabUploadBtn = document.getElementById('tab-upload-btn');
    const tabCameraBtn = document.getElementById('tab-camera-btn');
    const sectionUpload = document.getElementById('section-upload');
    const sectionCamera = document.getElementById('section-camera');
    
    const dropzone = document.getElementById('dropzone');
    const fileInput = document.getElementById('file-input');
    const startCameraBtn = document.getElementById('start-camera-btn');
    const snapCameraBtn = document.getElementById('snap-camera-btn');
    const webcamStream = document.getElementById('webcam-stream');
    const previewPhoto = document.getElementById('preview-photo');
    const placeholderMsg = document.getElementById('placeholder-message');
    const editorStage = document.getElementById('editor-stage');
    const overlayFrame = document.getElementById('overlay-frame');
    
    const frameOptionsContainer = document.getElementById('frame-options-container');
    const controlsAdjustment = document.getElementById('controls-adjustment');
    const inputZoom = document.getElementById('input-zoom');
    const valZoom = document.getElementById('val-zoom');
    const inputRotate = document.getElementById('input-rotate');
    const valRotate = document.getElementById('val-rotate');
    
    const processImageBtn = document.getElementById('process-image-btn');
    const postProcessOptions = document.getElementById('post-process-options');
    const downloadZipBtn = document.getElementById('download-zip-btn');
    const shareQrBtn = document.getElementById('share-qr-btn');
    const openEmailModalBtn = document.getElementById('open-email-modal-btn');
    
    // Modals
    const qrModal = document.getElementById('qr-modal');
    const closeQrBtn = document.getElementById('close-qr-btn');
    const qrCodeTarget = document.getElementById('qr-code-target');
    const shareLinkAnchor = document.getElementById('share-link-anchor');
    
    const emailModal = document.getElementById('email-modal');
    const closeEmailBtn = document.getElementById('close-email-btn');
    const emailForm = document.getElementById('email-form');
    const emailInput = document.getElementById('email-input');
    const submitEmailBtn = document.getElementById('submit-email-btn');
    const emailFeedback = document.getElementById('email-feedback');
    
    // Canvas
    const offscreenCanvas = document.getElementById('offscreen-canvas');
    
    // Polaroid Mode Elements
    const modeDefaultBtn = document.getElementById('mode-default-btn');
    const modePolaroidBtn = document.getElementById('mode-polaroid-btn');
    const polaroidPreviewStage = document.getElementById('polaroid-preview-stage');
    const countdownOverlay = document.getElementById('countdown-overlay');
    const countdownNumber = document.getElementById('countdown-number');
    
    // Camera Control Overlay elements
    const cameraControlsOverlay = document.getElementById('camera-controls-overlay');
    const switchCameraBtn = document.getElementById('switch-camera-btn');
    const snapOverlayBtn = document.getElementById('snap-overlay-btn');
    const snapOverlayBtnText = document.getElementById('snap-overlay-btn-text');
    const stopCameraOverlayBtn = document.getElementById('stop-camera-overlay-btn');

    // --- Application State ---
    let currentTab = 'upload'; // 'upload' | 'camera'
    let currentMode = 'default'; // 'default' | 'polaroid'
    let selectedFrame = 'frame.png';
    let imageLoaded = false;
    let baseScale = 1.0;
    
    // Image Transformations (for single photo mode)
    let scale = 1.0;
    let rotation = 0; // degrees
    let panX = 0;
    let panY = 0;
    
    // Webcam stream variables
    let localStream = null;
    let currentFacingMode = 'user'; // 'user' (front) | 'environment' (back)
    
    // Polaroid photos array (max 4 base64 images)
    let polaroidPhotos = [];
    let polaroidSnapCount = 0;
    
    // Mouse/Touch Drag state
    let isDragging = false;
    let startX = 0;
    let startY = 0;
    
    // Final Base64 outputs
    let rawBase64 = null;
    let framedBase64 = null;
    let currentUuid = null;
    let currentShareUrl = null;

    // --- Mode Switching ---
    modeDefaultBtn.addEventListener('click', () => {
        if (currentMode === 'default') return;
        currentMode = 'default';
        
        modeDefaultBtn.className = "flex-1 text-center py-2 rounded-xl text-xs font-bold transition-all duration-200 bg-slate-800 text-white shadow";
        modePolaroidBtn.className = "flex-1 text-center py-2 rounded-xl text-xs font-bold transition-all duration-200 text-slate-400 hover:text-white";
        
        fileInput.multiple = false;
        document.querySelector('#dropzone p.text-sm').textContent = "Klik untuk upload foto";
        document.querySelector('#dropzone p.text-xs').textContent = "atau seret dan lepas file di sini";
        
        overlayFrame.classList.remove('hidden');
        polaroidPreviewStage.classList.add('hidden');
        
        resetEditor();
    });

    modePolaroidBtn.addEventListener('click', () => {
        if (currentMode === 'polaroid') return;
        currentMode = 'polaroid';
        
        modePolaroidBtn.className = "flex-1 text-center py-2 rounded-xl text-xs font-bold transition-all duration-200 bg-slate-800 text-white shadow";
        modeDefaultBtn.className = "flex-1 text-center py-2 rounded-xl text-xs font-bold transition-all duration-200 text-slate-400 hover:text-white";
        
        fileInput.multiple = true;
        document.querySelector('#dropzone p.text-sm').textContent = "Klik untuk upload 4 foto";
        document.querySelector('#dropzone p.text-xs').textContent = "atau seret dan lepas hingga 4 file sekaligus";
        
        overlayFrame.classList.add('hidden');
        polaroidPreviewStage.classList.remove('hidden');
        
        resetEditor();
    });

    // --- Tab Switching ---
    tabUploadBtn.addEventListener('click', () => {
        if (currentTab === 'upload') return;
        currentTab = 'upload';
        
        tabUploadBtn.className = "flex-1 text-center py-2.5 rounded-xl text-sm font-semibold transition-all duration-200 bg-slate-800 text-white shadow";
        tabCameraBtn.className = "flex-1 text-center py-2.5 rounded-xl text-sm font-semibold transition-all duration-200 text-slate-400 hover:text-white";
        
        sectionUpload.classList.remove('hidden');
        sectionCamera.classList.add('hidden');
        
        stopCamera();
        resetEditor();
    });

    tabCameraBtn.addEventListener('click', () => {
        if (currentTab === 'camera') return;
        currentTab = 'camera';
        
        tabCameraBtn.className = "flex-1 text-center py-2.5 rounded-xl text-sm font-semibold transition-all duration-200 bg-slate-800 text-white shadow";
        tabUploadBtn.className = "flex-1 text-center py-2.5 rounded-xl text-sm font-semibold transition-all duration-200 text-slate-400 hover:text-white";
        
        sectionCamera.classList.remove('hidden');
        sectionUpload.classList.add('hidden');
        
        resetEditor();
    });

    // --- Camera Logic ---
    async function startCamera() {
        try {
            // Stop any existing stream
            stopCamera();
            
            localStream = await navigator.mediaDevices.getUserMedia({
                video: { width: 1080, height: 1080, facingMode: currentFacingMode },
                audio: false
            });
            
            webcamStream.srcObject = localStream;
            previewPhoto.classList.add('hidden');
            placeholderMsg.classList.add('hidden');
            
            if (currentMode === 'polaroid') {
                polaroidSnapCount = 0;
                updatePolaroidActiveCameraSlot();
            } else {
                if (webcamStream.parentElement !== editorStage) {
                    editorStage.appendChild(webcamStream);
                }
                webcamStream.classList.remove('hidden');
            }
            
            if (currentFacingMode === 'user') {
                webcamStream.classList.add('transform', 'scale-x-[-1]');
            } else {
                webcamStream.classList.remove('transform', 'scale-x-[-1]');
            }
            
            // Show overlay controls
            cameraControlsOverlay.classList.remove('hidden');
            
            // Enable snap buttons
            snapCameraBtn.disabled = false;
            snapCameraBtn.className = "flex-1 bg-rose-500 hover:bg-rose-600 text-white text-sm font-semibold py-3 px-4 rounded-xl transition-all duration-200 flex items-center justify-center gap-2 cursor-pointer";
            
            snapOverlayBtn.disabled = false;
            snapOverlayBtn.className = "flex-1 bg-rose-500 hover:bg-rose-600 text-white font-bold py-3.5 px-6 rounded-2xl transition-all duration-200 flex items-center justify-center gap-2 shadow-lg shadow-rose-500/30 cursor-pointer";

            if (currentMode === 'polaroid') {
                polaroidSnapCount = 0;
                const snapText = `Ambil Foto 1 (0/4)`;
                snapCameraBtn.innerHTML = `
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    ${snapText}
                `;
                snapOverlayBtnText.textContent = snapText;
            } else {
                const snapText = "Ambil Foto";
                snapCameraBtn.innerHTML = `
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    ${snapText}
                `;
                snapOverlayBtnText.textContent = snapText;
            }
            
            startCameraBtn.textContent = "Restart Kamera";
        } catch (error) {
            console.error("Camera access failed:", error);
            alert("Gagal mengakses kamera. Harap pastikan izin kamera diberikan.");
        }
    }

    startCameraBtn.addEventListener('click', startCamera);

    switchCameraBtn.addEventListener('click', async () => {
        currentFacingMode = currentFacingMode === 'user' ? 'environment' : 'user';
        await startCamera();
    });

    snapOverlayBtn.addEventListener('click', () => {
        if (!snapOverlayBtn.disabled) {
            snapCameraBtn.click();
        }
    });

    stopCameraOverlayBtn.addEventListener('click', () => {
        stopCamera();
        resetEditor();
    });

    snapCameraBtn.addEventListener('click', () => {
        if (!localStream) return;
        
        if (currentMode === 'polaroid') {
            captureSinglePolaroidShot();
        } else {
            // Grab current video dimensions
            const videoWidth = webcamStream.videoWidth || 1080;
            const videoHeight = webcamStream.videoHeight || 1080;
            
            // Use a temporary canvas to freeze the frame
            const tempCanvas = document.createElement('canvas');
            tempCanvas.width = videoWidth;
            tempCanvas.height = videoHeight;
            const tempCtx = tempCanvas.getContext('2d');
            
            // Draw video frame to canvas (flipped horizontally for user mirror effect only)
            if (currentFacingMode === 'user') {
                tempCtx.translate(videoWidth, 0);
                tempCtx.scale(-1, 1);
            }
            tempCtx.drawImage(webcamStream, 0, 0, videoWidth, videoHeight);
            
            const dataUrl = tempCanvas.toDataURL('image/jpeg', 0.85);
            
            // Set preview photo
            previewPhoto.src = dataUrl;
            previewPhoto.classList.remove('hidden');
            webcamStream.classList.add('hidden');
            
            // Stop stream
            stopCamera();
            
            // Initialize photo placement
            previewPhoto.onload = () => {
                imageLoaded = true;
                initializePhotoTransform();
            };
        }
    });

    function captureSinglePolaroidShot() {
        if (polaroidSnapCount >= 4) return;

        // Disable snap buttons during countdown
        snapCameraBtn.disabled = true;
        snapCameraBtn.className = "flex-1 bg-slate-900 border border-slate-800 text-slate-500 text-sm font-semibold py-3 px-4 rounded-xl transition-all duration-200 flex items-center justify-center gap-2 cursor-not-allowed";

        snapOverlayBtn.disabled = true;
        snapOverlayBtn.className = "flex-1 bg-slate-900 border border-slate-800 text-slate-500 font-bold py-3.5 px-6 rounded-2xl transition-all duration-200 flex items-center justify-center gap-2 cursor-not-allowed";

        countdownOverlay.classList.remove('hidden');

        let secondsLeft = 3;
        countdownNumber.textContent = secondsLeft;

        const intervalId = setInterval(() => {
            secondsLeft--;
            if (secondsLeft > 0) {
                countdownNumber.textContent = secondsLeft;
            } else {
                clearInterval(intervalId);

                // Flash effect
                countdownOverlay.classList.add('bg-white');
                setTimeout(() => {
                    countdownOverlay.classList.remove('bg-white');
                }, 80);

                // Freeze webcam frame
                const videoWidth = webcamStream.videoWidth || 1080;
                const videoHeight = webcamStream.videoHeight || 1080;
                const tempCanvas = document.createElement('canvas');
                tempCanvas.width = videoWidth;
                tempCanvas.height = videoHeight;
                const tempCtx = tempCanvas.getContext('2d');
                
                if (currentFacingMode === 'user') {
                    tempCtx.translate(videoWidth, 0);
                    tempCtx.scale(-1, 1);
                }
                tempCtx.drawImage(webcamStream, 0, 0, videoWidth, videoHeight);

                const dataUrl = tempCanvas.toDataURL('image/jpeg', 0.85);
                polaroidPhotos[polaroidSnapCount] = dataUrl;

                // Update Slot UI
                const slotDiv = document.querySelector(`[data-slot="${polaroidSnapCount}"]`);
                if (slotDiv) {
                    const slotImg = slotDiv.querySelector('img:not([data-frame-overlay])');
                    const frameOverlay = slotDiv.querySelector('img[data-frame-overlay]');
                    const slotSpan = slotDiv.querySelector('span');

                    if (slotImg) {
                        slotImg.src = dataUrl;
                        slotImg.classList.remove('hidden');
                    }
                    if (frameOverlay) {
                        frameOverlay.classList.remove('hidden');
                    }
                    if (slotSpan) slotSpan.classList.add('hidden');
                }

                polaroidSnapCount++;
                countdownOverlay.classList.add('hidden');

                if (polaroidSnapCount < 4) {
                    // Update camera position to next slot
                    updatePolaroidActiveCameraSlot();

                    // Re-enable button for the next shot
                    snapCameraBtn.disabled = false;
                    snapCameraBtn.className = "flex-1 bg-rose-500 hover:bg-rose-600 text-white text-sm font-semibold py-3 px-4 rounded-xl transition-all duration-200 flex items-center justify-center gap-2 cursor-pointer";
                    const snapText = `Ambil Foto ${polaroidSnapCount + 1} (${polaroidSnapCount}/4)`;
                    snapCameraBtn.innerHTML = `
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        ${snapText}
                    `;
                    
                    snapOverlayBtn.disabled = false;
                    snapOverlayBtn.className = "flex-1 bg-rose-500 hover:bg-rose-600 text-white font-bold py-3.5 px-6 rounded-2xl transition-all duration-200 flex items-center justify-center gap-2 shadow-lg shadow-rose-500/30 cursor-pointer";
                    snapOverlayBtnText.textContent = snapText;
                } else {
                    // All 4 shots captured!
                    stopCamera();
                    webcamStream.classList.add('hidden');
                    polaroidPreviewStage.classList.remove('hidden');

                    imageLoaded = true;
                    processImageBtn.disabled = false;
                    processImageBtn.className = "w-full bg-gradient-to-r from-rose-500 to-indigo-600 hover:from-rose-600 hover:to-indigo-700 text-white py-4 px-6 rounded-2xl font-bold text-sm transition-all duration-300 shadow-lg shadow-rose-500/20 hover:scale-[1.01] hover:shadow-xl hover:shadow-rose-500/30 cursor-pointer";

                    snapCameraBtn.innerHTML = `
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        4 Foto Selesai
                    `;
                }
            }
        }, 1000);
    }

    function stopCamera() {
        if (localStream) {
            localStream.getTracks().forEach(track => track.stop());
            localStream = null;
        }
        if (webcamStream.parentElement !== editorStage) {
            editorStage.appendChild(webcamStream);
        }
        webcamStream.srcObject = null;
        webcamStream.classList.add('hidden');
        cameraControlsOverlay.classList.add('hidden'); // Hide overlay
        
        snapCameraBtn.disabled = true;
        snapCameraBtn.className = "flex-1 bg-slate-900 border border-slate-800 text-slate-500 text-sm font-semibold py-3 px-4 rounded-xl transition-all duration-200 flex items-center justify-center gap-2 cursor-not-allowed";
        
        snapOverlayBtn.disabled = true;
        snapOverlayBtn.className = "flex-1 bg-slate-900 border border-slate-800 text-slate-500 font-bold py-3.5 px-6 rounded-2xl transition-all duration-200 flex items-center justify-center gap-2 cursor-not-allowed";
        
        startCameraBtn.innerHTML = `
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 00-2 2z" />
            </svg>
            Nyalakan Kamera
        `;
    }

    // --- Upload Logic ---
    dropzone.addEventListener('click', () => fileInput.click());

    fileInput.addEventListener('change', (e) => {
        if (currentMode === 'polaroid') {
            handleMultipleFiles(e.target.files);
        } else {
            const file = e.target.files[0];
            if (file) handleImageFile(file);
        }
    });

    // Drag and Drop
    ['dragenter', 'dragover'].forEach(eventName => {
        dropzone.addEventListener(eventName, (e) => {
            e.preventDefault();
            dropzone.classList.add('border-rose-500', 'bg-slate-950/40');
        }, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropzone.addEventListener(eventName, (e) => {
            e.preventDefault();
            dropzone.classList.remove('border-rose-500', 'bg-slate-950/40');
        }, false);
    });

    dropzone.addEventListener('drop', (e) => {
        if (currentMode === 'polaroid') {
            handleMultipleFiles(e.dataTransfer.files);
        } else {
            const file = e.dataTransfer.files[0];
            if (file && file.type.startsWith('image/')) {
                handleImageFile(file);
            }
        }
    });

    function handleImageFile(file) {
        const reader = new FileReader();
        reader.onload = async (event) => {
            try {
                const compressed = await compressImage(event.target.result);
                previewPhoto.src = compressed;
                previewPhoto.classList.remove('hidden');
                placeholderMsg.classList.add('hidden');
                
                previewPhoto.onload = () => {
                    imageLoaded = true;
                    initializePhotoTransform();
                };
            } catch (err) {
                console.error("Compression error:", err);
                alert("Gagal memproses gambar.");
            }
        };
        reader.readAsDataURL(file);
    }

    function handleMultipleFiles(files) {
        if (!files || files.length === 0) return;
        
        // Take at most 4 files
        const selectedFiles = Array.from(files).slice(0, 4);
        
        // Reset state
        polaroidPhotos = [];
        resetPolaroidSlots();
        
        let loadedCount = 0;
        selectedFiles.forEach((file, index) => {
            if (!file.type.startsWith('image/')) return;
            
            const reader = new FileReader();
            reader.onload = async (event) => {
                try {
                    const compressedBase64 = await compressImage(event.target.result);
                    polaroidPhotos[index] = compressedBase64;
                    
                    // Update HTML slot UI
                    const slotDiv = document.querySelector(`[data-slot="${index}"]`);
                    if (slotDiv) {
                        const slotImg = slotDiv.querySelector('img:not([data-frame-overlay])');
                        const frameOverlay = slotDiv.querySelector('img[data-frame-overlay]');
                        const slotSpan = slotDiv.querySelector('span');
                        
                        if (slotImg) {
                            slotImg.src = compressedBase64;
                            slotImg.classList.remove('hidden');
                        }
                        if (frameOverlay) {
                            frameOverlay.classList.remove('hidden');
                        }
                        if (slotSpan) slotSpan.classList.add('hidden');
                    }
                } catch (err) {
                    console.error("Compression error:", err);
                } finally {
                    loadedCount++;
                    if (loadedCount === selectedFiles.length) {
                        if (polaroidPhotos.filter(p => p).length === 4) {
                            imageLoaded = true;
                            processImageBtn.disabled = false;
                            processImageBtn.className = "w-full bg-gradient-to-r from-rose-500 to-indigo-600 hover:from-rose-600 hover:to-indigo-700 text-white py-4 px-6 rounded-2xl font-bold text-sm transition-all duration-300 shadow-lg shadow-rose-500/20 hover:scale-[1.01] hover:shadow-xl hover:shadow-rose-500/30 cursor-pointer";
                        } else {
                            alert("Harap pilih/unggah tepat 4 foto untuk mode Polaroid.");
                        }
                    }
                }
            };
            reader.readAsDataURL(file);
        });
    }

    function updatePolaroidActiveCameraSlot() {
        if (currentMode === 'polaroid' && localStream) {
            const activeSlotDiv = document.querySelector(`[data-slot="${polaroidSnapCount}"]`);
            if (activeSlotDiv) {
                activeSlotDiv.appendChild(webcamStream);
                webcamStream.classList.remove('hidden');
                const slotSpan = activeSlotDiv.querySelector('span');
                if (slotSpan) slotSpan.classList.add('hidden');
            }
        }
    }

    function resetPolaroidSlots() {
        if (webcamStream.parentElement !== editorStage) {
            editorStage.appendChild(webcamStream);
        }
        for (let i = 0; i < 4; i++) {
            const slotDiv = document.querySelector(`[data-slot="${i}"]`);
            if (slotDiv) {
                const slotImg = slotDiv.querySelector('img:not([data-frame-overlay])');
                const frameOverlay = slotDiv.querySelector('img[data-frame-overlay]');
                const slotSpan = slotDiv.querySelector('span');
                
                if (slotImg) {
                    slotImg.src = "";
                    slotImg.classList.add('hidden');
                }
                if (frameOverlay) {
                    frameOverlay.classList.add('hidden');
                }
                if (slotSpan) {
                    slotSpan.classList.remove('hidden');
                    slotSpan.textContent = `Foto ${i + 1}`;
                }
            }
        }
    }

    // --- Frame Customizer Selection ---
    frameOptionsContainer.addEventListener('click', (e) => {
        const button = e.target.closest('.frame-selector');
        if (!button) return;
        
        // Remove active class from all
        document.querySelectorAll('.frame-selector').forEach(btn => {
            btn.classList.remove('border-rose-500', 'shadow-lg');
            btn.classList.add('border-slate-800', 'shadow');
        });
        
        // Add active style to selected
        button.classList.remove('border-slate-800', 'shadow');
        button.classList.add('border-rose-500', 'shadow-lg');
        
        // Update source
        selectedFrame = button.getAttribute('data-frame');
        overlayFrame.src = `/frames/${selectedFrame}`;
    });

    // --- Photo Transformation & Panning Math ---
    function initializePhotoTransform() {
        const stageWidth = editorStage.clientWidth;
        const stageHeight = editorStage.clientHeight;
        
        const photoWidth = previewPhoto.naturalWidth;
        const photoHeight = previewPhoto.naturalHeight;
        
        // Compute base scale to fit the photo inside the 1:1 stage
        baseScale = Math.min(stageWidth / photoWidth, stageHeight / photoHeight);
        
        // Reset transformation values
        scale = 1.0;
        rotation = 0;
        panX = 0;
        panY = 0;
        
        // Reset controls
        inputZoom.value = 1.0;
        valZoom.textContent = "100%";
        inputRotate.value = 0;
        valRotate.textContent = "0°";
        
        // Show adjustments section
        controlsAdjustment.classList.remove('hidden');
        
        // Enable Process Button
        processImageBtn.disabled = false;
        processImageBtn.className = "w-full bg-rose-500 hover:bg-rose-600 text-white font-bold py-4 px-6 rounded-2xl text-sm transition-all duration-300 flex items-center justify-center gap-2 cursor-pointer shadow-lg shadow-rose-500/10";
        
        // Hide previous process options if any
        postProcessOptions.classList.add('hidden');
        
        updatePhotoDOMTransform();
    }

    function updatePhotoDOMTransform() {
        if (!imageLoaded) return;
        const totalScale = scale * baseScale;
        previewPhoto.style.transform = `translate(${panX}px, ${panY}px) scale(${totalScale}) rotate(${rotation}deg)`;
    }

    // Sliders
    inputZoom.addEventListener('input', (e) => {
        scale = parseFloat(e.target.value);
        valZoom.textContent = `${Math.round(scale * 100)}%`;
        updatePhotoDOMTransform();
    });

    inputRotate.addEventListener('input', (e) => {
        rotation = parseInt(e.target.value);
        valRotate.textContent = `${rotation}°`;
        updatePhotoDOMTransform();
    });

    // Panning (Drag and Drop image) inside Editor Stage
    editorStage.addEventListener('mousedown', (e) => {
        if (!imageLoaded) return;
        isDragging = true;
        startX = e.clientX - panX;
        startY = e.clientY - panY;
    });

    window.addEventListener('mousemove', (e) => {
        if (!isDragging) return;
        panX = e.clientX - startX;
        panY = e.clientY - startY;
        updatePhotoDOMTransform();
    });

    window.addEventListener('mouseup', () => {
        isDragging = false;
    });

    // Touch Support for Mobile devices
    editorStage.addEventListener('touchstart', (e) => {
        if (!imageLoaded) return;
        isDragging = true;
        const touch = e.touches[0];
        startX = touch.clientX - panX;
        startY = touch.clientY - panY;
    });

    window.addEventListener('touchmove', (e) => {
        if (!isDragging) return;
        const touch = e.touches[0];
        panX = touch.clientX - startX;
        panY = touch.clientY - startY;
        updatePhotoDOMTransform();
    });

    window.addEventListener('touchend', () => {
        isDragging = false;
    });

    // --- Reset Editor ---
    function resetEditor() {
        imageLoaded = false;
        previewPhoto.src = "";
        previewPhoto.classList.add('hidden');
        if (currentMode === 'default') {
            placeholderMsg.classList.remove('hidden');
        } else {
            placeholderMsg.classList.add('hidden');
        }
        controlsAdjustment.classList.add('hidden');
        
        // Reset Polaroid variables
        polaroidPhotos = [];
        polaroidSnapCount = 0;
        resetPolaroidSlots();
        if (currentMode === 'polaroid') {
            snapCameraBtn.innerHTML = `
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                Ambil Foto 1 (0/4)
            `;
        } else {
            snapCameraBtn.innerHTML = `
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                Ambil Foto
            `;
        }
        
        processImageBtn.disabled = true;
        processImageBtn.className = "w-full bg-slate-900 text-slate-500 border border-slate-800 py-4 px-6 rounded-2xl font-bold text-sm transition-all duration-300 cursor-not-allowed flex items-center justify-center gap-2";
        processImageBtn.textContent = "Proses Foto";
        
        postProcessOptions.classList.add('hidden');
    }

    const loadImage = (src) => {
        return new Promise((resolve, reject) => {
            const img = new Image();
            img.crossOrigin = "anonymous";
            img.onload = () => resolve(img);
            img.onerror = (e) => reject(new Error("Gagal memuat gambar: " + src));
            img.src = src;
        });
    };

    function drawImageCover(ctx, img, x, y, w, h) {
        const imgRatio = img.naturalWidth / img.naturalHeight;
        const targetRatio = w / h;
        let sx = 0, sy = 0, sw = img.naturalWidth, sh = img.naturalHeight;
        if (imgRatio > targetRatio) {
            sw = sh * targetRatio;
            sx = (img.naturalWidth - sw) / 2;
        } else {
            sh = sw / targetRatio;
            sy = (img.naturalHeight - sh) / 2;
        }
        ctx.drawImage(img, sx, sy, sw, sh, x, y, w, h);
    }

    // --- Canvas Composition & Merge ---
    processImageBtn.addEventListener('click', async () => {
        if (!imageLoaded) return;
        
        // Show loading state on button
        processImageBtn.disabled = true;
        processImageBtn.innerHTML = `
            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Memproses Gambar...
        `;

        try {
            const ctx = offscreenCanvas.getContext('2d');

            if (currentMode === 'polaroid') {
                // Resize offscreen canvas for collage (using 1450px height)
                offscreenCanvas.width = 1200;
                offscreenCanvas.height = 1450;
                ctx.clearRect(0, 0, 1200, 1450);

                // Fill background with solid red (matching preview's bg-red-600)
                ctx.fillStyle = '#dc2626';
                ctx.fillRect(0, 0, 1200, 1450);

                // Load photos and assets in parallel (loading the static qr.png directly)
                const [logoImg, qrImg, pImg1, pImg2, pImg3, pImg4] = await Promise.all([
                    loadImage('/logo/Hanari.png'),
                    loadImage('/qr-code/qr.png'),
                    loadImage(polaroidPhotos[0]),
                    loadImage(polaroidPhotos[1]),
                    loadImage(polaroidPhotos[2]),
                    loadImage(polaroidPhotos[3])
                ]);

                const photos = [pImg1, pImg2, pImg3, pImg4];
                // x gap = 20px, y gap = 30px (providing vertical separation between rows)
                const slots = [
                    { x: 50, y: 50 },
                    { x: 610, y: 50 },
                    { x: 50, y: 620 },
                    { x: 610, y: 620 }
                ];

                // Draw each photo (size 540x540)
                for (let i = 0; i < 4; i++) {
                    const slot = slots[i];
                    const photo = photos[i];
                    ctx.save();
                    drawImageCover(ctx, photo, slot.x, slot.y, 540, 540);
                    ctx.restore();
                }

                // Draw a decorative divider line (white with low opacity)
                ctx.strokeStyle = 'rgba(255, 255, 255, 0.2)';
                ctx.lineWidth = 4;
                ctx.beginPath();
                ctx.moveTo(50, 1195);
                ctx.lineTo(1150, 1195);
                ctx.stroke();

                // Draw Hanari text logo (on the left side) - matched to h-20 size relative to canvas
                const logoHeight = 260;
                const logoWidth = logoHeight * (logoImg.naturalWidth / logoImg.naturalHeight);
                const logoX = 100;
                const logoY = 1170;
                ctx.drawImage(logoImg, logoX, logoY, logoWidth, logoHeight);

                // Draw QR Code (on the right side) - matched to h-10 size (exactly half of logo height)
                const qrSize = 130;
                const qrX = 1200 - 100 - qrSize; // 970
                const qrY = 1235;
                ctx.drawImage(qrImg, qrX, qrY, qrSize, qrSize);

                // Save collage base64
                framedBase64 = offscreenCanvas.toDataURL('image/jpeg', 0.85);

                // Zip raw photos + collage client-side
                const zip = new JSZip();
                for (let i = 0; i < 4; i++) {
                    const rawBlob = dataURLtoBlob(polaroidPhotos[i]);
                    zip.file(`foto_asli_${i + 1}.png`, rawBlob);
                }
                const collageBlob = dataURLtoBlob(framedBase64);
                zip.file("hanari_polaroid_collage.png", collageBlob);

                const zipBlob = await zip.generateAsync({ type: "blob" });
                
                // Read zipBlob as base64 dataUrl
                const reader = new FileReader();
                const zipBase64 = await new Promise((resolve) => {
                    reader.onloadend = () => resolve(reader.result);
                    reader.readAsDataURL(zipBlob);
                });
                rawBase64 = zipBase64;

            } else {
                // Default Mode
                offscreenCanvas.width = 1200;
                offscreenCanvas.height = 1200;
                ctx.clearRect(0, 0, 1200, 1200);

                const stageWidth = editorStage.clientWidth;
                const multiplier = 1200 / stageWidth;

                ctx.save();
                ctx.translate(600 + panX * multiplier, 600 + panY * multiplier);
                ctx.rotate((rotation * Math.PI) / 180);
                ctx.scale(scale * baseScale * multiplier, scale * baseScale * multiplier);
                ctx.drawImage(previewPhoto, -previewPhoto.naturalWidth / 2, -previewPhoto.naturalHeight / 2);
                ctx.restore();

                rawBase64 = offscreenCanvas.toDataURL('image/jpeg', 0.85);

                const frameImg = await loadImage(`/frames/${selectedFrame}`);
                ctx.drawImage(frameImg, 0, 0, 1200, 1200);
                framedBase64 = offscreenCanvas.toDataURL('image/jpeg', 0.85);
            }

            // Post both assets to backend
            const response = await fetch('/api/photos', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    raw_image: rawBase64,
                    framed_image: framedBase64,
                    mode: currentMode
                })
            });

            const result = await response.json();

            if (response.ok && result.success) {
                currentUuid = result.uuid;
                currentShareUrl = result.share_url;

                processImageBtn.className = "w-full bg-emerald-500 text-white font-bold py-4 px-6 rounded-2xl text-sm transition-all duration-300 flex items-center justify-center gap-2 cursor-default";
                processImageBtn.innerHTML = `
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5.5 w-5.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Foto Berhasil Diproses!
                `;

                // Set download caption details dynamically
                const downloadCaption = document.querySelector('#post-process-options p.text-slate-500');
                if (downloadCaption) {
                    if (currentMode === 'polaroid') {
                        downloadCaption.innerHTML = "Bundel ZIP berisi 5 file: <strong>kanvas collage</strong> dan <strong>4 foto asli</strong> Anda.";
                    } else {
                        downloadCaption.innerHTML = "Bundel ZIP berisi 2 file: <strong>foto_dengan_bingkai.png</strong> dan <strong>foto_asli.png</strong>";
                    }
                }

                postProcessOptions.classList.remove('hidden');

                // Generate QR Code
                qrCodeTarget.innerHTML = "";
                QRCode.toDataURL(currentShareUrl, {
                    width: 180,
                    margin: 1,
                    color: {
                        dark: "#0f172a",
                        light: "#ffffff"
                    }
                }, function (err, url) {
                    if (err) {
                        console.error("QR Code generation error:", err);
                        return;
                    }
                    qrCodeTarget.innerHTML = `<img src="${url}" alt="QR Code" class="w-[180px] h-[180px] rounded-xl">`;
                });
                shareLinkAnchor.href = currentShareUrl;
                shareLinkAnchor.textContent = currentShareUrl;

            } else {
                throw new Error(result.message || "Gagal menyimpan foto di server.");
            }

        } catch (error) {
            console.error("Processing error:", error);
            alert("Terjadi kesalahan saat memproses foto: " + error.message);
            
            processImageBtn.disabled = false;
            processImageBtn.className = "w-full bg-rose-500 hover:bg-rose-600 text-white font-bold py-4 px-6 rounded-2xl text-sm transition-all duration-300 flex items-center justify-center gap-2 cursor-pointer shadow-lg shadow-rose-500/10";
            processImageBtn.textContent = "Proses Ulang Foto";
        }
    });

    // --- ZIP Compilation & Download ---
    downloadZipBtn.addEventListener('click', async () => {
        if (!rawBase64 || !framedBase64) return;

        if (currentMode === 'polaroid') {
            // rawBase64 is already the base64 encoded ZIP file
            const zipBlob = dataURLtoBlob(rawBase64);
            const url = window.URL.createObjectURL(zipBlob);
            const a = document.createElement('a');
            a.href = url;
            a.download = "hanari_polaroid_photos.zip";
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            window.URL.revokeObjectURL(url);
        } else {
            const rawBlob = dataURLtoBlob(rawBase64);
            const framedBlob = dataURLtoBlob(framedBase64);

            const zip = new JSZip();
            zip.file("foto_asli.png", rawBlob);
            zip.file("foto_dengan_bingkai.png", framedBlob);

            try {
                const content = await zip.generateAsync({ type: "blob" });
                const url = window.URL.createObjectURL(content);
                const a = document.createElement('a');
                a.href = url;
                a.download = "automatic_frame_photos.zip";
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                window.URL.revokeObjectURL(url);
            } catch (err) {
                console.error("ZIP Generation failed:", err);
                alert("Gagal membuat bundel ZIP.");
            }
        }
    });

    // Helper to compress base64 image client-side to keep payloads small
    function compressImage(base64Str, maxWidth = 1080, maxHeight = 1080, quality = 0.8) {
        return new Promise((resolve, reject) => {
            const img = new Image();
            img.src = base64Str;
            img.onload = () => {
                let width = img.naturalWidth;
                let height = img.naturalHeight;
                
                if (width > maxWidth || height > maxHeight) {
                    if (width > height) {
                        height = Math.round((height * maxWidth) / width);
                        width = maxWidth;
                    } else {
                        width = Math.round((width * maxHeight) / height);
                        height = maxHeight;
                    }
                }
                
                const canvas = document.createElement('canvas');
                canvas.width = width;
                canvas.height = height;
                const ctx = canvas.getContext('2d');
                ctx.drawImage(img, 0, 0, width, height);
                resolve(canvas.toDataURL('image/jpeg', quality));
            };
            img.onerror = (err) => reject(new Error("Gagal mengompresi gambar"));
        });
    }

    // Helper to convert base64 to Blob
    function dataURLtoBlob(dataurl) {
        const arr = dataurl.split(',');
        const mime = arr[0].match(/:(.*?);/)[1];
        const bstr = atob(arr[1]);
        let n = bstr.length;
        const u8arr = new Uint8Array(n);
        while (n--) {
            u8arr[n] = bstr.charCodeAt(n);
        }
        return new Blob([u8arr], { type: mime });
    }

    // --- QR Modal Management ---
    shareQrBtn.addEventListener('click', () => {
        qrModal.classList.remove('hidden');
        setTimeout(() => qrModal.classList.add('opacity-100'), 50);
    });

    closeQrBtn.addEventListener('click', () => {
        qrModal.classList.remove('opacity-100');
        setTimeout(() => qrModal.classList.add('hidden'), 300);
    });

    // --- Email Modal Management ---
    openEmailModalBtn.addEventListener('click', () => {
        emailModal.classList.remove('hidden');
        setTimeout(() => emailModal.classList.add('opacity-100'), 50);
        emailFeedback.classList.add('hidden');
    });

    closeEmailBtn.addEventListener('click', () => {
        emailModal.classList.remove('opacity-100');
        setTimeout(() => emailModal.classList.add('hidden'), 300);
    });

    emailForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        submitEmailBtn.disabled = true;
        submitEmailBtn.textContent = "Mengirim...";
        emailFeedback.className = "text-xs text-center text-slate-400 font-medium";
        emailFeedback.textContent = "Sedang mengirim email...";
        emailFeedback.classList.remove('hidden');

        try {
            const response = await fetch(`/share/${currentUuid}/mail`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ email: emailInput.value })
            });

            const result = await response.json();

            if (response.ok && result.success) {
                emailFeedback.className = "text-xs text-center text-emerald-400 font-bold";
                emailFeedback.textContent = "Foto berhasil dikirim ke email!";
                emailInput.value = "";
            } else {
                throw new Error(result.message || "Gagal mengirim email.");
            }
        } catch (error) {
            emailFeedback.className = "text-xs text-center text-rose-500 font-bold";
            emailFeedback.textContent = error.message;
        } finally {
            submitEmailBtn.disabled = false;
            submitEmailBtn.textContent = "Kirim Sekarang";
        }
    });
});
