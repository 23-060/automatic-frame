<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Hanari Automatic Frame - Pasang Bingkai Foto Instan</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    <!-- Style sheet (Vite) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen flex flex-col justify-between antialiased selection:bg-rose-500 selection:text-white">
    
    <!-- Header -->
    <header class="border-b border-slate-900 bg-slate-950/80 backdrop-blur-md sticky top-0 z-50">
        <div class="max-w-6xl mx-auto px-6 py-4 flex items-center justify-between">
            <div class="flex items-center gap-2 group">
                <img src="/logo/Hanari Logo.png" alt="Hanari Logo" class="w-8 h-8 object-contain group-hover:scale-105 transition-all duration-300">
                <span class="font-semibold text-lg tracking-wider bg-clip-text text-transparent bg-gradient-to-r from-white to-slate-400">HANARI COMMUNITY</span>
            </div>
        </div>
    </header>

    <!-- Main Workspace -->
    <main class="max-w-6xl mx-auto px-6 py-12 flex-1 flex flex-col gap-12 w-full">


        <!-- App Container -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            
            <!-- Sidebar Controls (5 Cols) -->
            <div class="lg:col-span-5 flex flex-col gap-6 order-2 lg:order-1">
                
                <!-- Mode Selector (Default vs Polaroid) -->
                <div class="flex flex-col gap-2">
                    <label class="text-xs font-semibold tracking-wider text-slate-400 uppercase">Mode Foto</label>
                    <div class="bg-slate-900 border border-slate-900 rounded-2xl p-1.5 flex gap-1">
                        <button id="mode-default-btn" class="flex-1 text-center py-2 rounded-xl text-xs font-bold transition-all duration-200 bg-slate-800 text-white shadow">
                            Default (1 Foto)
                        </button>
                        <button id="mode-polaroid-btn" class="flex-1 text-center py-2 rounded-xl text-xs font-bold transition-all duration-200 text-slate-400 hover:text-white">
                            Polaroid (4 Foto)
                        </button>
                    </div>
                </div>

                <!-- Tab: Upload vs Camera -->
                <div class="bg-slate-900 border border-slate-900 rounded-2xl p-2 flex gap-1">
                    <button id="tab-upload-btn" class="flex-1 text-center py-2.5 rounded-xl text-sm font-semibold transition-all duration-200 bg-slate-800 text-white shadow">
                        Upload Foto
                    </button>
                    <button id="tab-camera-btn" class="flex-1 text-center py-2.5 rounded-xl text-sm font-semibold transition-all duration-200 text-slate-400 hover:text-white">
                        Kamera Langsung
                    </button>
                </div>

                <!-- Input Options Card -->
                <div class="bg-slate-900/50 border border-slate-900 rounded-3xl p-6 flex flex-col gap-6 backdrop-blur-md">
                    
                    <!-- Upload Section -->
                    <div id="section-upload" class="flex flex-col gap-4">
                        <label class="text-xs font-semibold tracking-wider text-slate-400 uppercase">Pilih File Foto</label>
                        <div id="dropzone" class="border-2 border-dashed border-slate-800 hover:border-rose-500/50 rounded-2xl p-8 text-center cursor-pointer transition-all duration-300 group bg-slate-950/20 hover:bg-slate-950/40">
                            <input type="file" id="file-input" accept="image/*" class="hidden">
                            <div class="flex flex-col items-center gap-3">
                                <div class="w-12 h-12 rounded-xl bg-slate-900 border border-slate-800 flex items-center justify-center text-slate-400 group-hover:text-rose-500 group-hover:scale-110 transition-all duration-300">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-slate-200">Klik untuk upload foto</p>
                                    <p class="text-xs text-slate-500 mt-1">atau seret dan lepas file di sini</p>
                                </div>
                                <span class="text-[10px] bg-slate-900 px-2.5 py-1 rounded-full text-slate-400 font-medium">PNG, JPG, WEBP</span>
                            </div>
                        </div>
                    </div>

                    <!-- Camera Section -->
                    <div id="section-camera" class="hidden flex flex-col gap-4">
                        <label class="text-xs font-semibold tracking-wider text-slate-400 uppercase">Kontrol Kamera</label>
                        <div class="flex gap-2">
                            <button id="start-camera-btn" class="flex-1 bg-rose-500 hover:bg-rose-600 text-white text-sm font-semibold py-3 px-4 rounded-xl transition-all duration-200 shadow-lg shadow-rose-500/10 flex items-center justify-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 00-2 2z" />
                                </svg>
                                Nyalakan Kamera
                            </button>
                            <button id="snap-camera-btn" disabled class="flex-1 bg-slate-900 border border-slate-800 text-slate-500 text-sm font-semibold py-3 px-4 rounded-xl transition-all duration-200 flex items-center justify-center gap-2 cursor-not-allowed">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                    <circle cx="12" cy="13" r="3" />
                                </svg>
                                Ambil Foto
                            </button>
                        </div>
                    </div>

                    <!-- Frame Selection -->
                    <div class="flex flex-col gap-3">
                        <label class="text-xs font-semibold tracking-wider text-slate-400 uppercase">Pilih Bingkai (Frame)</label>
                        <div class="grid grid-cols-4 gap-3" id="frame-options-container">
                            <!-- Default Frame -->
                            <button class="frame-selector border-2 border-rose-500 rounded-xl overflow-hidden aspect-square bg-slate-950 p-1 flex flex-col items-center justify-center group relative shadow-lg" data-frame="frame.png">
                                <div class="w-full h-full border border-slate-800 rounded bg-slate-900 flex items-center justify-center">
                                    <span class="text-slate-400 text-[10px] font-semibold">frame.png</span>
                                </div>
                                <span class="text-[9px] mt-1 text-slate-400 font-semibold absolute bottom-1 truncate max-w-[90%] bg-slate-950/90 px-1 rounded">Default</span>
                            </button>
                        </div>
                    </div>

                    <!-- Photo Adjustments (Zoom / Pan / Rotate) -->
                    <div id="controls-adjustment" class="hidden flex flex-col gap-4 border-t border-slate-900 pt-4">
                        <label class="text-xs font-semibold tracking-wider text-slate-400 uppercase">Atur Posisi Foto</label>
                        
                        <!-- Zoom Slider -->
                        <div class="flex flex-col gap-1.5">
                            <div class="flex justify-between text-xs text-slate-400 font-medium">
                                <span>Perbesar (Zoom)</span>
                                <span id="val-zoom">100%</span>
                            </div>
                            <input type="range" id="input-zoom" min="0.1" max="3" step="0.05" value="1"
                                   class="w-full h-1.5 bg-slate-800 rounded-lg appearance-none cursor-pointer accent-rose-500">
                        </div>

                        <!-- Rotate Slider -->
                        <div class="flex flex-col gap-1.5">
                            <div class="flex justify-between text-xs text-slate-400 font-medium">
                                <span>Rotasi (Derajat)</span>
                                <span id="val-rotate">0°</span>
                            </div>
                            <input type="range" id="input-rotate" min="-180" max="180" step="1" value="0"
                                   class="w-full h-1.5 bg-slate-800 rounded-lg appearance-none cursor-pointer accent-rose-500">
                        </div>

                        <p class="text-[11px] text-slate-500 leading-relaxed italic">
                            Klik dan seret (drag) foto di panel pratinjau kanan untuk menggeser posisi foto secara manual.
                        </p>
                    </div>

                </div>

            </div>

            <!-- Preview & Execution Panel (7 Cols) -->
            <div class="lg:col-span-7 flex flex-col gap-6 items-center order-1 lg:order-2">
                
                <!-- Live Canvas Preview Window -->
                <div class="w-full max-w-[450px] aspect-square relative rounded-3xl overflow-hidden bg-slate-900 border border-slate-900 shadow-2xl p-1 shadow-rose-500/5 group flex items-center justify-center">
                    
                    <!-- Editor Interactive Stage -->
                    <div id="editor-stage" class="w-full h-full relative overflow-hidden rounded-2xl bg-slate-950 cursor-grab active:cursor-grabbing">
                        
                        <!-- Video stream for Camera Tab -->
                        <video id="webcam-stream" autoplay playsinline class="hidden w-full h-full object-cover absolute inset-0 transform scale-x-[-1]"></video>
                        
                        <!-- Image render container for Upload Tab -->
                        <div id="photo-container" class="absolute inset-0 flex items-center justify-center origin-center select-none pointer-events-none">
                            <img id="preview-photo" src="" class="hidden max-w-none origin-center transform" style="transform: translate(0px, 0px) scale(1) rotate(0deg);">
                            
                            <!-- Placeholder message -->
                            <div id="placeholder-message" class="text-center p-6 flex flex-col items-center gap-3">
                                <div class="w-16 h-16 rounded-full bg-slate-900 border border-slate-800 flex items-center justify-center text-slate-400">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <p class="text-sm font-semibold text-slate-300">Belum ada foto terpilih</p>
                                <p class="text-xs text-slate-500 max-w-[200px]">Silakan unggah foto atau nyalakan kamera terlebih dahulu</p>
                            </div>
                        </div>

                        <!-- Polaroid Mode Preview Stage -->
                        <div id="polaroid-preview-stage" class="hidden absolute inset-0 bg-slate-950 flex items-center justify-center p-3 z-0">
                            <div class="w-[330px] h-fit bg-red-600 rounded-2xl p-3.5 flex flex-col justify-between shadow-2xl">
                                <div class="grid grid-cols-2 gap-2.5">
                                    <!-- Slot 1 -->
                                    <div class="relative bg-slate-50 border border-slate-200 rounded-lg overflow-hidden flex items-center justify-center aspect-square" data-slot="0">
                                        <span class="text-[10px] text-slate-400 font-bold uppercase">Foto 1</span>
                                        <img class="absolute inset-0 w-full h-full object-cover hidden">
                                    </div>
                                    <!-- Slot 2 -->
                                    <div class="relative bg-slate-50 border border-slate-200 rounded-lg overflow-hidden flex items-center justify-center aspect-square" data-slot="1">
                                        <span class="text-[10px] text-slate-400 font-bold uppercase">Foto 2</span>
                                        <img class="absolute inset-0 w-full h-full object-cover hidden">
                                    </div>
                                    <!-- Slot 3 -->
                                    <div class="relative bg-slate-50 border border-slate-200 rounded-lg overflow-hidden flex items-center justify-center aspect-square" data-slot="2">
                                        <span class="text-[10px] text-slate-400 font-bold uppercase">Foto 3</span>
                                        <img class="absolute inset-0 w-full h-full object-cover hidden">
                                    </div>
                                    <!-- Slot 4 -->
                                    <div class="relative bg-slate-50 border border-slate-200 rounded-lg overflow-hidden flex items-center justify-center aspect-square" data-slot="3">
                                        <span class="text-[10px] text-slate-400 font-bold uppercase">Foto 4</span>
                                        <img class="absolute inset-0 w-full h-full object-cover hidden">
                                    </div>
                                </div>
                                <!-- Bottom Banner preview representing the Hanari text logo and QR Code -->
                                <div class="border-t border-white/20 flex items-center justify-between bg-transparent rounded-xl">
                                    <div class="flex flex-col gap-0.5 items-start">
                                        <img src="/logo/Hanari.png" alt="Hanari Logo" class="h-20 object-contain">
                                    </div>
                                    <div class="p-1 bg-white border border-slate-150 rounded-md">
                                        <img src="/qr-code/qr.png" alt="Hanari QR" class="h-10 object-contain">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Absolute Overlay Frame Image -->
                        <img id="overlay-frame" src="/frames/frame.png" class="absolute inset-0 w-full h-full object-fill pointer-events-none z-10">

                        <!-- Countdown Overlay -->
                        <div id="countdown-overlay" class="absolute inset-0 bg-black/50 flex items-center justify-center hidden z-20">
                            <span id="countdown-number" class="text-white text-7xl font-extrabold tracking-tighter">3</span>
                        </div>

                    </div>

                </div>

                <!-- Process / Download Action Panel -->
                <div class="w-full max-w-[450px] flex flex-col gap-3">
                    <button id="process-image-btn" disabled class="w-full bg-slate-900 text-slate-500 border border-slate-800 py-4 px-6 rounded-2xl font-bold text-sm transition-all duration-300 cursor-not-allowed flex items-center justify-center gap-2">
                        <span>Proses Foto</span>
                    </button>

                    <!-- Post-Process Options Card -->
                    <div id="post-process-options" class="hidden bg-slate-900/40 border border-slate-900 rounded-3xl p-6 flex flex-col gap-4 w-full">
                        
                        <span class="text-xs font-semibold tracking-wider text-slate-400 uppercase">Hasil Foto Anda</span>
                        
                        <!-- Instant Zip Download -->
                        <button id="download-zip-btn" class="w-full bg-gradient-to-r from-rose-500 to-rose-600 hover:from-rose-600 hover:to-rose-700 text-white font-bold py-4 px-6 rounded-2xl transition-all duration-300 shadow-lg shadow-rose-500/20 hover:scale-[1.01] flex items-center justify-center gap-2.5">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            Download Hasil (ZIP)
                        </button>
                        
                        <p class="text-[10px] text-slate-500 text-center leading-normal mt-[-5px]">
                            Bundel ZIP berisi 2 file: <strong>foto_dengan_bingkai.png</strong> dan <strong>foto_asli.png</strong>
                        </p>

                        <div class="grid grid-cols-2 gap-3 mt-2">
                            <!-- Share via QR -->
                            <button id="share-qr-btn" class="flex items-center justify-center gap-2 py-3 px-4 bg-slate-900 border border-slate-800 hover:border-slate-700 text-slate-200 text-sm font-semibold rounded-xl transition-all duration-200">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4.5 w-4.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                                </svg>
                                Scan QR Code
                            </button>

                            <!-- Send Email -->
                            <button id="open-email-modal-btn" class="flex items-center justify-center gap-2 py-3 px-4 bg-slate-900 border border-slate-800 hover:border-slate-700 text-slate-200 text-sm font-semibold rounded-xl transition-all duration-200">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4.5 w-4.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                                Kirim ke Email
                            </button>
                        </div>

                    </div>
                </div>

            </div>

        </div>

    </main>

    <!-- QR Code Modal Backdrop -->
    <div id="qr-modal" class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 flex items-center justify-center hidden opacity-0 transition-opacity duration-300">
        <div class="bg-slate-900 border border-slate-800 max-w-sm w-full mx-6 rounded-3xl p-8 flex flex-col items-center gap-6 shadow-2xl relative">
            <button id="close-qr-btn" class="absolute top-4 right-4 text-slate-500 hover:text-white transition-colors duration-200">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
            <div class="text-center">
                <h3 class="text-lg font-bold text-white">Scan untuk Download</h3>
                <p class="text-xs text-slate-400 mt-1">Gunakan kamera ponsel Anda untuk memindai QR code ini dan mengunduh foto di HP.</p>
            </div>
            
            <!-- QR Target Container -->
            <div id="qr-code-target" class="p-4 bg-white rounded-2xl flex items-center justify-center shadow-lg w-[200px] h-[200px]"></div>

            <div class="w-full flex flex-col gap-2">
                <a id="share-link-anchor" href="#" target="_blank" class="text-xs text-rose-500 text-center font-medium hover:underline break-all">Link share</a>
            </div>
        </div>
    </div>

    <!-- Email Modal Backdrop -->
    <div id="email-modal" class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 flex items-center justify-center hidden opacity-0 transition-opacity duration-300">
        <div class="bg-slate-900 border border-slate-800 max-w-md w-full mx-6 rounded-3xl p-8 flex flex-col gap-6 shadow-2xl relative">
            <button id="close-email-btn" class="absolute top-4 right-4 text-slate-500 hover:text-white transition-colors duration-200">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
            <div>
                <h3 class="text-lg font-bold text-white">Kirim Hasil via Email</h3>
                <p class="text-xs text-slate-400 mt-1">Kami akan mengirimkan foto dengan bingkai dan foto asli sebagai lampiran.</p>
            </div>

            <form id="email-form" class="flex flex-col gap-4">
                <div class="flex flex-col gap-2">
                    <label for="email-input" class="text-xs font-semibold text-slate-400 uppercase">Alamat Email</label>
                    <input type="email" id="email-input" placeholder="contoh@domain.com" required
                           class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-rose-500/50 placeholder:text-slate-700 transition-colors duration-200">
                </div>
                <button type="submit" id="submit-email-btn"
                        class="w-full bg-gradient-to-r from-rose-500 to-rose-600 hover:from-rose-600 hover:to-rose-700 text-white font-bold py-3.5 px-6 rounded-xl transition-all duration-300 flex items-center justify-center gap-2">
                    Kirim Sekarang
                </button>
            </form>

            <div id="email-feedback" class="text-xs text-center hidden"></div>
        </div>
    </div>

    <!-- Hidden composite canvas used for offscreen frame rendering (1200x1200px) -->
    <canvas id="offscreen-canvas" width="1200" height="1200" class="hidden"></canvas>

</body>
</html>
