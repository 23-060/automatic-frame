<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Foto Bareng Anda</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            background-color: #F8F9FA;
            color: #333333;
            margin: 0;
            padding: 0;
            -webkit-font-smoothing: antialiased;
        }
        .container {
            max-width: 600px;
            margin: 40px auto;
            background-color: #FFFFFF;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            border: 1px solid #EAEAEA;
        }
        .header {
            background-color: #1A1A19;
            padding: 40px 20px;
            text-align: center;
            color: #FFFFFF;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 700;
            letter-spacing: 2px;
        }
        .content {
            padding: 40px 30px;
            line-height: 1.6;
        }
        .content h2 {
            font-size: 20px;
            margin-top: 0;
            color: #1A1A19;
        }
        .content p {
            font-size: 16px;
            color: #555555;
            margin-bottom: 24px;
        }
        .btn {
            display: inline-block;
            background-color: #1A1A19;
            color: #FFFFFF !important;
            text-decoration: none;
            padding: 14px 30px;
            font-size: 16px;
            font-weight: 600;
            border-radius: 8px;
            margin: 20px 0;
            text-align: center;
            letter-spacing: 1px;
            transition: background-color 0.2s ease;
        }
        .btn:hover {
            background-color: #333333;
        }
        .footer {
            background-color: #F8F9FA;
            padding: 20px 30px;
            text-align: center;
            font-size: 12px;
            color: #A0A0A0;
            border-top: 1px solid #EAEAEA;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>HANARI COMMUNITY</h1>
        </div>
        <div class="content">
            <h2>Halo Kak!</h2>
            <p>Terima kasih banyak sudah berkunjung ke booth <strong>Hanari Community</strong>! Seru sekali bisa mengabadikan momen berharga Anda di booth kami.</p>
            
            <div style="background-color: #FFF2F4; border-left: 4px solid #FF85A1; padding: 15px 20px; border-radius: 6px; margin-bottom: 25px;">
                <p style="margin: 0; font-weight: 600; color: #D14D72; font-size: 15px;">📢 Yuk Gabung Komunitas Hanari!</p>
                <p style="margin: 5px 0 0 0; font-size: 13.5px; color: #5F5F5F;">Jangan lupa gabung komunitas kita untuk info event, gathering, kelas, dan keseruan lainnya di sini: 
                    <a href="https://www.instagram.com/hanari.ofc/" target="_blank" style="color: #FF85A1; font-weight: bold; text-decoration: underline;">Hanari Community</a>
                </p>
            </div>
            
            @php $isPolaroid = str_ends_with($photo->raw_path, '.zip'); @endphp

            @if($isPolaroid)
                <p>Kami telah melampirkan foto kolase Polaroid Anda langsung di email ini.</p>
                <p>Untuk mengunduh semua file foto asli beserta kolasenya dalam bentuk file <strong>ZIP</strong>, silakan klik tombol di bawah ini:</p>
                <div style="text-align: center; margin: 20px 0;">
                    <a href="{{ asset('storage/' . $photo->raw_path) }}" class="btn" style="background-color: #dc2626; color: #ffffff !important;" target="_blank">UNDUH FILE ZIP</a>
                </div>
            @else
                <p>Kami telah melampirkan foto berbingkai Anda langsung di email ini.</p>
                <p>Untuk mengunduh foto asli tanpa bingkai, silakan klik tombol di bawah ini:</p>
                <div style="text-align: center; margin: 20px 0;">
                    <a href="{{ asset('storage/' . $photo->raw_path) }}" class="btn" style="background-color: #1A1A19; color: #ffffff !important;" target="_blank">UNDUH FOTO ASLI</a>
                </div>
            @endif

            <p>Anda juga dapat melihat, mengunduh, atau membagikan kembali foto tersebut secara online melalui halaman publik di bawah ini:</p>
            
            <div style="text-align: center; margin: 20px 0;">
                <a href="{{ route('share.show', $photo->uuid) }}" class="btn" style="background-color: #1A1A19; color: #ffffff !important;" target="_blank">LIHAT FOTO ONLINE</a>
            </div>
            
            <p>Halaman ini akan tetap aktif sehingga Anda dapat mengakses foto Anda kapan saja.</p>
        </div>
    </div>
</body>
</html>
