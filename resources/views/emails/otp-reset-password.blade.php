<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kode OTP Reset Password</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: #f1f5f9;
            margin: 0;
            padding: 24px;
            color: #1e293b;
        }
        .container {
            max-width: 520px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }
        .header {
            background-color: #0284c7;
            padding: 28px 24px;
            text-align: center;
            color: #ffffff;
        }
        .header h1 {
            margin: 0;
            font-size: 20px;
            font-weight: 700;
        }
        .header p {
            margin: 6px 0 0;
            font-size: 13px;
            opacity: 0.9;
        }
        .body {
            padding: 28px 24px;
        }
        .greeting {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 12px;
        }
        .text {
            font-size: 14px;
            line-height: 1.6;
            color: #475569;
            margin-bottom: 20px;
        }
        .otp-box {
            background-color: #f0f9ff;
            border: 2px dashed #0284c7;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            margin: 24px 0;
        }
        .otp-label {
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #0369a1;
            margin-bottom: 8px;
        }
        .otp-code {
            font-size: 32px;
            font-weight: 800;
            letter-spacing: 6px;
            color: #0c4a6e;
            font-family: 'Courier New', Courier, monospace;
        }
        .btn-container {
            text-align: center;
            margin: 28px 0;
        }
        .btn {
            display: inline-block;
            background-color: #0284c7;
            color: #ffffff !important;
            text-decoration: none;
            padding: 12px 28px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
        }
        .note {
            font-size: 12px;
            color: #64748b;
            border-top: 1px solid #e2e8f0;
            padding-top: 16px;
            margin-top: 24px;
            line-height: 1.5;
        }
        .footer {
            background-color: #f8fafc;
            padding: 16px 24px;
            text-align: center;
            font-size: 12px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>RAPI-Sekolah</h1>
            <p>Sistem Pengelolaan Ruang & Agenda Fasilitas Sekolah</p>
        </div>
        <div class="body">
            <div class="greeting">Halo, {{ $user->fullname ?? 'Pengguna' }}!</div>
            <p class="text">
                Kami menerima permintaan untuk mengatur ulang kata sandi akun Anda. Gunakan kode verifikasi (OTP) berikut untuk melanjutkan:
            </p>

            <div class="otp-box">
                <div class="otp-label">Kode Verifikasi (OTP)</div>
                <div class="otp-code">{{ $otp }}</div>
            </div>

            <p class="text" style="text-align: center; font-size: 13px;">
                Atau klik tautan langsung di bawah ini untuk mengatur ulang kata sandi:
            </p>

            <div class="btn-container">
                <a href="{{ $resetUrl }}" class="btn">Atur Ulang Kata Sandi</a>
            </div>

            <div class="note">
                <p>• Kode OTP dan tautan ini berlaku selama <strong>60 menit</strong>.</p>
                <p>• Jika Anda tidak meminta pengaturan ulang kata sandi, abaikan email ini. Akun Anda tetap aman.</p>
            </div>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} RAPI-Sekolah • SMKN 1 Bantul. Seluruh hak cipta dilindungi.
        </div>
    </div>
</body>
</html>

