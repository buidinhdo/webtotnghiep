<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mã xác thực đăng nhập GameStation</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: #f1f5f9;
            color: #1e293b;
            margin: 0;
            padding: 0;
            line-height: 1.6;
        }
        .wrapper {
            width: 100%;
            background-color: #f1f5f9;
            padding: 30px 15px;
            box-sizing: border-box;
        }
        .container {
            max-width: 540px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.07), 0 2px 4px -2px rgba(0, 0, 0, 0.05);
            border: 1px solid #e2e8f0;
        }
        .header {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            color: #ffffff;
            padding: 28px 24px;
            text-align: center;
        }
        .brand-name {
            font-size: 24px;
            font-weight: 800;
            letter-spacing: -0.5px;
            color: #38bdf8;
            margin: 0 0 6px 0;
        }
        .brand-subtitle {
            font-size: 13px;
            color: #94a3b8;
            margin: 0;
        }
        .content {
            padding: 32px 28px;
        }
        .greeting {
            font-size: 16px;
            color: #334155;
            margin-bottom: 16px;
        }
        .message {
            font-size: 14px;
            color: #475569;
            margin-bottom: 24px;
        }
        .otp-container {
            text-align: center;
            background: #f8fafc;
            border: 2px dashed #0284c7;
            border-radius: 10px;
            padding: 20px 16px;
            margin: 24px 0;
        }
        .otp-title {
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #64748b;
            margin-bottom: 8px;
        }
        .otp-code {
            font-family: 'Courier New', Courier, monospace;
            font-size: 34px;
            font-weight: 800;
            letter-spacing: 8px;
            color: #0284c7;
            margin: 0;
            user-select: all;
        }
        .otp-expiration {
            font-size: 13px;
            color: #dc2626;
            font-weight: 600;
            margin-top: 8px;
        }
        .warning-box {
            background-color: #fffbeb;
            border-left: 4px solid #f59e0b;
            padding: 12px 16px;
            border-radius: 6px;
            margin: 20px 0;
            font-size: 13px;
            color: #92400e;
        }
        .footer {
            background-color: #f8fafc;
            border-top: 1px solid #e2e8f0;
            padding: 20px 24px;
            text-align: center;
            font-size: 12px;
            color: #64748b;
        }
        .footer p {
            margin: 4px 0;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container">
            <div class="header">
                <div class="brand-name">GAMESTATION</div>
                <div class="brand-subtitle">Cổng game & Thiết bị chơi game chính hãng</div>
            </div>

            <div class="content">
                <p class="greeting">Xin chào <strong>{{ $userName }}</strong>,</p>
                <p class="message">
                    Bạn vừa thực hiện yêu cầu đăng nhập vào tài khoản GameStation. Vui lòng sử dụng mã xác thực OTP dưới đây để hoàn tất quá trình đăng nhập:
                </p>

                <div class="otp-container">
                    <div class="otp-title">Mã xác thực OTP</div>
                    <div class="otp-code">{{ $otp }}</div>
                    <div class="otp-expiration">Mã có hiệu lực trong vòng 5 phút</div>
                </div>

                <div class="warning-box">
                    <strong>Lưu ý bảo mật:</strong> Tuyệt đối không chia sẻ mã này cho bất kỳ ai. GameStation không bao giờ yêu cầu bạn cung cấp mã xác thực này qua điện thoại hoặc tin nhắn.
                </div>

                <p style="font-size: 13px; color: #64748b; margin-top: 20px;">
                    Nếu bạn không thực hiện yêu cầu này, có thể ai đó đang cố gắng truy cập tài khoản của bạn. Vui lòng đổi mật khẩu ngay để bảo vệ tài khoản.
                </p>
            </div>

            <div class="footer">
                <p>Email này được gửi tự động từ hệ thống GameStation.</p>
                <p>&copy; {{ date('Y') }} GameStation. All rights reserved.</p>
            </div>
        </div>
    </div>
</body>
</html>
