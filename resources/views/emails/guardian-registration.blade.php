<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Guardian Registration Request</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #f5f5f5;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .content {
            background-color: #fff;
            padding: 15px;
            border-radius: 5px;
            border: 1px solid #ddd;
        }
        .footer {
            margin-top: 20px;
            font-size: 12px;
            color: #777;
            text-align: center;
        }
        h1 {
            color: #333;
            font-size: 24px;
        }
        .field {
            margin-bottom: 15px;
        }
        .field-label {
            font-weight: bold;
            display: block;
            margin-bottom: 5px;
        }
        .field-value {
            padding: 10px;
            background-color: #f9f9f9;
            border-radius: 3px;
        }
        .button {
            display: inline-block;
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            text-align: center;
            text-decoration: none;
            font-size: 16px;
            border-radius: 5px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Guardian Registration Request</h1>
    </div>

    <div class="content">
        <p>Hello {{ $guardianName }},</p>

        <p>{{ $childName }} has registered you as their guardian on our platform. As a guardian, you'll be able to monitor and control their activities.</p>

        <p>To complete your registration and set up your guardian account, please click the button below. You will need to:</p>

        <ul style="margin: 15px 0; padding-left: 20px;">
            <li>Provide your phone number</li>
            <li>Enter your birthday (you must be at least 18 years old)</li>
            <li>Create a password for your account</li>
        </ul>

        <div style="text-align: center;">
            <a href="{{ $registerUrl }}" class="button">Complete Registration</a>
        </div>

        <p>If you did not expect this request or have any questions, please contact our support team.</p>
    </div>

    <div class="footer">
        <p>This email was sent from the Gaming Hub platform.</p>
    </div>
</body>
</html>
