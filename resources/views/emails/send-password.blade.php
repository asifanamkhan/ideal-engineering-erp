<!DOCTYPE html>
<html>
<head>
    <title>Your Login Credentials</title>
    <style>
        /* Reset CSS for email compatibility */
        body, table, td, a {
            -webkit-text-size-adjust: 100%;
            -ms-text-size-adjust: 100%;
        }
        table, td {
            mso-table-lspace: 0pt;
            mso-table-rspace: 0pt;
            border-collapse: collapse;
        }
        img {
            -ms-interpolation-mode: bicubic;
            border: 0;
            height: auto;
            line-height: 100%;
            outline: none;
            text-decoration: none;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
        }
        /* Main styles */
        body {
            background-color: #f7f7f7;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
        }
        .email-container {
            background-color: #ffffff;
            margin: 0 auto;
            padding: 0;
            width: 100%;
            max-width: 600px;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        .header {
            background: linear-gradient(135deg, #4b6cb7 0%, #182848 100%);
            padding: 25px 30px;
            text-align: center;
        }
        .logo {
            color: #ffffff;
            font-size: 24px;
            font-weight: bold;
            text-decoration: none;
        }
        .content {
            padding: 30px;
            color: #444444;
            line-height: 1.6;
        }
        .credentials-container {
            background-color: #f8f9fa;
            border-left: 4px solid #4b6cb7;
            padding: 15px;
            margin: 20px 0;
        }
        .credential-item {
            margin: 15px 0;
            padding: 10px;
            background-color: #ffffff;
            border-radius: 4px;
            border: 1px solid #eaeaea;
        }
        .password-display {
            font-size: 24px;
            font-weight: bold;
            letter-spacing: 2px;
            color: #182848;
            padding: 15px;
            margin: 15px 0;
            text-align: center;
            background-color: #f0f4ff;
            border-radius: 6px;
            border: 1px dashed #4b6cb7;
        }
        .footer {
            background-color: #182848;
            color: #ffffff;
            padding: 20px;
            text-align: center;
            font-size: 12px;
        }
        .button {
            background-color: #4b6cb7;
            border-radius: 4px;
            color: white;
            display: inline-block;
            font-size: 16px;
            font-weight: bold;
            margin: 20px 0;
            padding: 12px 30px;
            text-decoration: none;
        }
        .divider {
            border-top: 1px solid #eaeaea;
            margin: 25px 0;
        }
        .warning {
            background-color: #fff3e0;
            border-radius: 4px;
            color: #e65100;
            padding: 15px;
            font-size: 14px;
            margin: 20px 0;
            border-left: 4px solid #ff9800;
        }
        .info-box {
            background-color: #e8f5e9;
            border-radius: 4px;
            color: #2e7d32;
            padding: 15px;
            font-size: 14px;
            margin: 20px 0;
            border-left: 4px solid #4caf50;
        }
    </style>
</head>
<body>
    <center class="container">
        <table class="email-container" width="100%" cellpadding="0" cellspacing="0" border="0">
            <!-- Header -->
            <tr>
                <td class="header">
                    <a href="#" class="logo" style="color: #ffffff !important;">{{ config('app.name') }}</a>
                </td>
            </tr>

            <!-- Content -->
            <tr>
                <td class="content">
                    <h2>Your Account Credentials</h2>
                    <p>Hello <strong>{{ $name }}</strong>,</p>
                    <p>Your account has been successfully created. Here are your login details:</p>

                    <div class="credentials-container">
                        <div class="credential-item">
                            <strong>Email Address:</strong> {{ $email }}
                        </div>
                        <div class="credential-item">
                            <strong>Password:</strong>
                            <div class="password-display">{{ $password }}</div>
                        </div>
                    </div>

                    <div class="info-box">
                        <strong>Quick Start:</strong> Use the credentials above to log in to your account.
                    </div>

                    <div class="warning">
                        <strong>Security Notice:</strong> Please log in and change your password immediately for security reasons.
                    </div>

                    <div style="text-align: center;">
                        <a href="{{ url('/login') }}" class="button" style="color: white">Login to Your Account</a>
                    </div>

                    <div class="divider"></div>

                    <p>If you have any questions, please contact our support team.</p>

                    <p>Best regards,<br>The {{ config('app.name') }} Team</p>
                </td>
            </tr>

            <!-- Footer -->
            <tr>
                <td class="footer">
                    <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
                </td>
            </tr>
        </table>
    </center>
</body>
</html>
