<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>@yield('title', 'CruseStick')</title>
    <!--[if mso]>
    <noscript>
        <xml>
            <o:OfficeDocumentSettings>
                <o:PixelsPerInch>96</o:PixelsPerInch>
            </o:OfficeDocumentSettings>
        </xml>
    </noscript>
    <![endif]-->
    <style>
        /* Reset styles */
        body, table, td, p, a, li, blockquote {
            -webkit-text-size-adjust: 100%;
            -ms-text-size-adjust: 100%;
        }
        table, td {
            mso-table-lspace: 0pt;
            mso-table-rspace: 0pt;
        }
        img {
            -ms-interpolation-mode: bicubic;
            border: 0;
            height: auto;
            line-height: 100%;
            outline: none;
            text-decoration: none;
        }

        /* Base styles */
        body {
            margin: 0 !important;
            padding: 0 !important;
            background-color: #f8fafc;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
        }
        
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
        }
        
        .email-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 30px 20px;
            text-align: center;
        }
        
        .email-header h1 {
            margin: 0;
            color: #ffffff;
            font-size: 28px;
            font-weight: 600;
            line-height: 1.3;
        }
        
        .email-content {
            padding: 40px 30px;
            background-color: #ffffff;
        }
        
        .email-content h2 {
            color: #000000;
            font-size: 24px;
            font-weight: 700;
            margin: 0 0 20px 0;
            line-height: 1.3;
        }
        
        .email-content h3 {
            color: #1a202c;
            font-size: 20px;
            font-weight: 700;
            margin: 25px 0 15px 0;
            line-height: 1.3;
        }
        
        .email-content p {
            color: #1a202c;
            font-size: 16px;
            font-weight: 500;
            line-height: 1.6;
            margin: 0 0 16px 0;
        }
        
        .email-content strong {
            color: #000000;
            font-weight: 700;
        }
        
        .info-box {
            background-color: #ffffff;
            border: 2px solid #e2e8f0;
            border-left: 4px solid #4299e1;
            padding: 20px;
            margin: 25px 0;
            border-radius: 0 6px 6px 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        
        .info-box.success {
            background-color: #ffffff;
            border-color: #c6f6d5;
            border-left-color: #38a169;
        }
        
        .info-box.warning {
            background-color: #ffffff;
            border-color: #fed7aa;
            border-left-color: #ed8936;
        }
        
        .info-box h4 {
            color: #000000;
            font-size: 18px;
            font-weight: 700;
            margin: 0 0 12px 0;
            line-height: 1.3;
        }
        
        .info-box p {
            color: #1a202c;
            font-size: 16px;
            font-weight: 500;
            margin: 0 0 10px 0;
            line-height: 1.5;
        }
        
        .info-box strong {
            color: #000000;
            font-weight: 700;
        }
        
        .button {
            display: inline-block;
            padding: 14px 28px;
            background-color: #4299e1;
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            font-size: 16px;
            text-align: center;
            margin: 15px 0;
            transition: background-color 0.3s ease;
        }
        
        .button:hover {
            background-color: #3182ce !important;
        }
        
        .button.success {
            background-color: #38a169;
        }
        
        .button.success:hover {
            background-color: #2f855a !important;
        }
        
        .order-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            border-radius: 6px;
            overflow: hidden;
        }
        
        .order-table th {
            background-color: #edf2f7;
            color: #000000;
            font-weight: 700;
            padding: 16px 12px;
            text-align: left;
            font-size: 15px;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .order-table td {
            padding: 16px 12px;
            border-bottom: 1px solid #f7fafc;
            color: #1a202c;
            font-size: 15px;
            font-weight: 500;
            vertical-align: middle;
        }
        
        .order-table tr:last-child td {
            border-bottom: none;
        }
        
        .product-cell {
            display: flex;
            align-items: center;
        }
        
        .product-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 6px;
            margin-right: 12px;
            border: 1px solid #e2e8f0;
        }
        
        .product-details h5 {
            margin: 0 0 4px 0;
            color: #000000;
            font-size: 15px;
            font-weight: 700;
        }
        
        .badge {
            display: inline-block;
            background-color: #e53e3e;
            color: #ffffff;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            margin-left: 6px;
        }
        
        .total-section {
            background-color: #f7fafc;
            padding: 20px;
            border-radius: 6px;
            margin: 25px 0;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            color: #1a202c;
            font-size: 16px;
            font-weight: 500;
        }
        
        .total-row.final {
            border-top: 2px solid #e2e8f0;
            padding-top: 12px;
            margin-top: 12px;
            font-weight: 700;
            font-size: 20px;
            color: #000000;
        }
        
        .shipping-address {
            background-color: #ffffff;
            border: 2px solid #e2e8f0;
            padding: 20px;
            border-radius: 6px;
            margin: 20px 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        
        .shipping-address h4 {
            margin: 0 0 12px 0;
            color: #000000;
            font-size: 18px;
            font-weight: 700;
        }
        
        .shipping-address p {
            margin: 0;
            line-height: 1.6;
            color: #1a202c;
            font-size: 16px;
            font-weight: 500;
        }
        
        .shipping-address strong {
            color: #000000;
            font-weight: 700;
        }
        
        .email-footer {
            background-color: #2d3748;
            color: #a0aec0;
            padding: 30px 20px;
            text-align: center;
        }
        
        .email-footer p {
            margin: 0;
            font-size: 14px;
            line-height: 1.5;
        }
        
        .email-footer .company-name {
            color: #ffffff;
            font-weight: 600;
            font-size: 16px;
            margin-bottom: 8px;
        }
        
        /* Responsive styles */
        @media only screen and (max-width: 600px) {
            .email-container {
                width: 100% !important;
            }
            
            .email-content {
                padding: 25px 20px !important;
            }
            
            .email-header {
                padding: 25px 20px !important;
            }
            
            .email-header h1 {
                font-size: 24px !important;
            }
            
            .order-table th,
            .order-table td {
                padding: 12px 8px !important;
                font-size: 13px !important;
            }
            
            .product-cell {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .product-image {
                margin-right: 0 !important;
                margin-bottom: 8px !important;
            }
        }
        
        /* Dark mode support */
        @media (prefers-color-scheme: dark) {
            .email-container {
                background-color: #2d3748 !important;
            }
            
            .email-content {
                background-color: #2d3748 !important;
            }
            
            .email-content h2,
            .email-content h3 {
                color: #f7fafc !important;
            }
            
            .email-content p {
                color: #e2e8f0 !important;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <h1>@yield('header', 'CruseStick')</h1>
        </div>
        
        <div class="email-content">
            @yield('content')
        </div>
        
        <div class="email-footer">
            <p class="company-name">CruseStick</p>
            <p>&copy; {{ date('Y') }} CruseStick. All rights reserved.</p>
            <p>This email was sent to {{ $recipientEmail ?? 'you' }}.</p>
        </div>
    </div>
</body>
</html>