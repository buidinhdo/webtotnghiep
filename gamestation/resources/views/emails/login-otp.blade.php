<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mã xác thực OTP đăng nhập</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #334155;
            margin: 0;
            padding: 0;
            background-color: #f1f5f9;
        }
        .wrapper {
            width: 100%;
            padding: 30px 15px;
            box-sizing: border-box;
        }
        .container {
            max-width: 540px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            border: 1px solid #e2e8f0;
        }
        .header {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            color: #ffffff;
            padding: 28px 24px;
            text-align: center;
        }
        .brand {
            font-size: 22px;
            font-weight: 700;
            letter-spacing: 0.5px;
            color: #38bdf8;
            margin-bottom: 6px;
        }
        .title {
            margin: 0;
            font-size: 18px;
            font-weight: 600;
            color: #f8fafc;
        }
        .content {
            padding: 32px 28px;
        }
        .greeting {
            font-size: 16px;
            margin-top: 0;
            margin-bottom: 16px;
            color: #1e293b;
        }
        .instruction {
            font-size: 14px;
            color: #475569;
            margin-bottom: 24px;
        }
        .otp-box {
            background: #f8fafc;
            border: 2px dashed #0284c7;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            margin: 24px 0;
        }
        .otp-code {
            font-size: 36px;
            font-weight: 800;
            letter-spacing: 8px;
            color: #0284c7;
            margin: 0;
            font-family: 'Courier New', Courier, monospace;
        }
        .expiry-note {
            font-size: 13px;
            color: #64748b;
            margin-top: 8px;
            margin-bottom: 0;
        }
        .warning-box {
            background-color: #fef2f2;
            border-left: 4px solid #ef4444;
            padding: 12px 16px;
            border-radius: 4px;
            margin-top: 24px;
            font-size: 13px;
            color: #991b1b;
        }
        .footer {
            background-color: #f8fafc;
            padding: 20px 24px;
            text-align: center;
            font-size: 12px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container">
            <div class="header">
                <div class="brand">🎮 GAMESTATION</div>
                <h1 class="title">Xác thực đăng nhập 2 bước</h1>
            </div>
            
            <div class="content">
                <p class="greeting">Xin chào <strong>{{ $userName ?? 'Quý khách' }}</strong>,</p>
                <p class="instruction">
                    Chúng tôi nhận được yêu cầu đăng nhập vào tài khoản GameStation của bạn. Vui lòng sử dụng mã OTP dưới đây để hoàn tất đăng nhập:
                </p>
                
                <div class="otp-box">
                    <div class="otp-code">{{ $otp }}</div>
                    <p class="expiry-note">⏱️ Mã có hiệu lực trong vòng <strong>{{ $expiresMinutes }} phút</strong></p>
                </div>
            </div>
            
            <div class="footer">
                <p style="margin: 0 0 6px 0;">Email này được gửi tự động từ hệ thống GameStation.</p>
                <p style="margin: 0;">&copy; {{ date('Y') }} GameStation. All rights reserved.</p>
            </div>
        </div>
    </div>
</body>
</html>
