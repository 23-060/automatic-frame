<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Bagikan Foto - {{ config('app.name', 'Hanari Automatic Frame') }}</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    <!-- Style sheet (using Vite) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen flex flex-col justify-between antialiased selection:bg-rose-500 selection:text-white">
    
    <!-- Header -->
    <header class="border-b border-slate-900 bg-slate-950/80 backdrop-blur-md sticky top-0 z-50">
        <div class="max-w-4xl mx-auto px-6 py-4 flex items-center justify-between">
            <a href="/" class="flex items-center gap-2 group">
                <img src="/logo/Hanari Logo.png" alt="Hanari Logo" class="w-8 h-8 object-contain group-hover:scale-105 transition-all duration-300">
                <span class="font-semibold text-lg tracking-wider bg-clip-text text-transparent bg-gradient-to-r from-white to-slate-400">HANARI COMMUNITY</span>
            </a>
            <a href="/" class="text-sm text-slate-400 hover:text-white transition-colors duration-200 flex items-center gap-1.5 font-medium">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                Buat Baru
            </a>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-4xl mx-auto px-6 py-12 flex-1 flex flex-col items-center justify-center w-full">
        <div class="w-full grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
            
            <!-- Visual Card (Framed Image) -->
            <div class="flex flex-col items-center">
                <div class="relative group rounded-2xl overflow-hidden bg-slate-900 p-3 border border-slate-800 shadow-2xl shadow-rose-500/5 transition-all duration-500 hover:border-slate-700">
                    <img src="{{ $framed_url }}" alt="Foto dengan Bingkai" class="w-full max-w-[400px] h-auto rounded-xl object-contain shadow-lg">
                    <div class="absolute inset-0 bg-gradient-to-t from-slate-950/80 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-end justify-center pb-6">
                        <span class="text-xs bg-slate-900/90 border border-slate-700/80 px-3 py-1.5 rounded-full backdrop-blur-sm text-slate-300 shadow">Pratinjau Hasil</span>
                    </div>
                </div>
            </div>

            <!-- Actions and Info -->
            <div class="flex flex-col gap-8">
                <div>
                    <span class="text-rose-500 text-xs font-semibold tracking-wider uppercase">Foto Anda Siap!</span>
                </div>

                @php $isPolaroid = str_ends_with($photo->raw_path, '.zip'); @endphp
                <!-- Download Buttons -->
                <div class="flex flex-col gap-3">
                    @if($isPolaroid)
                        <!-- Download ZIP direct link for Polaroid Mode -->
                        <a href="{{ $raw_url }}" download="hanari_polaroid_photos.zip" 
                           class="w-full flex items-center justify-between bg-gradient-to-r from-rose-500 to-rose-600 hover:from-rose-600 hover:to-rose-700 text-white font-semibold py-4 px-6 rounded-xl transition-all duration-300 shadow-lg shadow-rose-500/20 hover:scale-[1.01] hover:shadow-xl hover:shadow-rose-500/30">
                            <span class="flex items-center gap-3">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5.5 w-5.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                </svg>
                                Download Hasil (ZIP)
                            </span>
                            <span class="text-xs bg-black/20 py-1 px-2.5 rounded-md text-white/90">ZIP</span>
                        </a>
                        <p class="text-[10px] text-slate-500 text-center leading-normal mt-[-5px]">
                            Bundel ZIP berisi 5 file: <strong>kanvas collage</strong> dan <strong>4 foto asli</strong> Anda.
                        </p>
                    @else
                        <!-- Download ZIP JS-based compile for Default Mode -->
                        <button id="download-zip-btn" 
                                data-framed="{{ $framed_url }}" 
                                data-raw="{{ $raw_url }}"
                                class="w-full flex items-center justify-between bg-gradient-to-r from-rose-500 to-rose-600 hover:from-rose-600 hover:to-rose-700 text-white font-semibold py-4 px-6 rounded-xl transition-all duration-300 shadow-lg shadow-rose-500/20 hover:scale-[1.01] hover:shadow-xl hover:shadow-rose-500/30 cursor-pointer">
                            <span class="flex items-center gap-3">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5.5 w-5.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                </svg>
                                Download Hasil (ZIP)
                            </span>
                            <span class="text-xs bg-black/20 py-1 px-2.5 rounded-md text-white/90">ZIP</span>
                        </button>
                        <p class="text-[10px] text-slate-500 text-center leading-normal mt-[-5px]">
                            Bundel ZIP berisi 2 file: <strong>foto_dengan_bingkai.png</strong> dan <strong>foto_asli.png</strong>
                        </p>
                    @endif
                </div>

                <!-- Email Sharing Form -->
                <div class="bg-slate-900/50 border border-slate-900 rounded-2xl p-6 backdrop-blur-md">
                    <h3 class="text-sm font-semibold text-slate-200 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4.5 w-4.5 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        Kirim Hasil ke Email
                    </h3>
                    <p class="text-xs text-slate-400 mt-1 leading-relaxed">
                        Kami akan mengirimkan kedua file foto ke alamat email Anda sebagai lampiran.
                    </p>

                    <form id="share-email-form" class="mt-4 flex gap-2" data-uuid="{{ $photo->uuid }}">
                        <input type="email" id="share-email-input" placeholder="Masukkan alamat email..." required
                               class="flex-1 bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-rose-500/50 placeholder:text-slate-600 transition-colors duration-200">
                        <button type="submit" id="share-email-button"
                                class="bg-rose-500 hover:bg-rose-600 active:scale-95 text-white font-medium text-sm px-5 py-3 rounded-xl transition-all duration-200 flex items-center gap-2 shadow-lg shadow-rose-500/10">
                            <span>Kirim</span>
                        </button>
                    </form>
                    
                    <!-- Alert Message -->
                    <div id="share-email-feedback" class="mt-3 text-xs hidden"></div>
                </div>

            </div>

        </div>
    </main>

    <!-- JSZip Library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>

    <!-- Inline Script for Share Page Email Submission and ZIP Download -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // ZIP Download Handler
            const downloadZipBtn = document.getElementById('download-zip-btn');
            if (downloadZipBtn) {
                const framedUrl = downloadZipBtn.getAttribute('data-framed');
                const rawUrl = downloadZipBtn.getAttribute('data-raw');

                downloadZipBtn.addEventListener('click', async () => {
                    const originalHTML = downloadZipBtn.innerHTML;
                    downloadZipBtn.disabled = true;
                    downloadZipBtn.innerHTML = `
                        <span class="flex items-center gap-3">
                            <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Menyiapkan ZIP...
                        </span>
                        <span class="text-xs bg-black/20 py-1 px-2.5 rounded-md text-white/90">ZIP</span>
                    `;

                    try {
                        // Fetch images
                        const [framedRes, rawRes] = await Promise.all([
                            fetch(framedUrl),
                            fetch(rawUrl)
                        ]);

                        if (!framedRes.ok || !rawRes.ok) throw new Error("Gagal mengambil file foto.");

                        const [framedBlob, rawBlob] = await Promise.all([
                            framedRes.blob(),
                            rawRes.blob()
                        ]);

                        const zip = new JSZip();
                        zip.file("foto_dengan_bingkai.png", framedBlob);
                        zip.file("foto_asli.png", rawBlob);

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
                        console.error("ZIP download failed:", err);
                        alert("Gagal mengunduh file ZIP: " + err.message);
                    } finally {
                        downloadZipBtn.disabled = false;
                        downloadZipBtn.innerHTML = originalHTML;
                    }
                });
            }

            // Email Form Submission Handler
            const form = document.getElementById('share-email-form');
            if (form) {
                const input = document.getElementById('share-email-input');
                const button = document.getElementById('share-email-button');
                const feedback = document.getElementById('share-email-feedback');
                const uuid = form.getAttribute('data-uuid');

                form.addEventListener('submit', async (e) => {
                    e.preventDefault();
                    
                    // Reset state
                    button.disabled = true;
                    button.innerHTML = '<span>Mengirim...</span>';
                    feedback.className = 'mt-3 text-xs text-slate-400';
                    feedback.innerText = 'Sedang mengirim email dengan lampiran...';
                    feedback.classList.remove('hidden');

                    try {
                        const response = await fetch(`/share/${uuid}/mail`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({ email: input.value })
                        });

                        const result = await response.json();

                        if (response.ok && result.success) {
                            feedback.className = 'mt-3 text-xs text-emerald-400 font-medium';
                            feedback.innerText = 'Foto berhasil dikirim! Silakan periksa inbox Anda (dan folder spam).';
                            input.value = '';
                        } else {
                            throw new Error(result.message || 'Gagal mengirim email.');
                        }
                    } catch (error) {
                        feedback.className = 'mt-3 text-xs text-rose-400 font-medium';
                        feedback.innerText = error.message || 'Terjadi kesalahan sistem saat mengirim email.';
                    } finally {
                        button.disabled = false;
                        button.innerHTML = '<span>Kirim</span>';
                    }
                });
            }
        });
    </script>
</body>
</html>
