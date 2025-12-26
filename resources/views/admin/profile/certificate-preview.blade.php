<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificate Preview</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Montserrat', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 30px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        /* A4 Paper Container - Landscape */
        .a4-paper {
            width: 1123px;
            height: 794px;
            background: white;
            box-shadow:
                0 0 0 1px rgba(0,0,0,0.1),
                0 10px 50px rgba(0,0,0,0.3),
                0 20px 100px rgba(0,0,0,0.2);
            position: relative;
            transform: scale(0.75);
            transform-origin: top center;
        }

        .certificate-container {
            width: 100%;
            height: 100%;
            background: white;
            border: 10px solid #B22234;
            padding: 30px 45px;
            position: relative;
        }

        .certificate-header {
            text-align: center;
            margin-bottom: 25px;
            border-bottom: 3px solid #B22234;
            padding-bottom: 15px;
        }

        .platform-title {
            font-size: 38px;
            font-weight: 700;
            color: #B22234;
            margin-bottom: 5px;
            letter-spacing: 3px;
        }

        .certificate-title {
            font-size: 24px;
            font-weight: 600;
            color: #333;
            text-transform: uppercase;
            letter-spacing: 4px;
        }

        .organizer-logo {
            position: absolute;
            top: 30px;
            right: 45px;
            max-width: 130px;
            max-height: 85px;
            object-fit: contain;
        }

        .certificate-body {
            text-align: center;
            padding: 30px 0 50px;
        }

        .label {
            font-size: 16px;
            color: #666;
            margin-bottom: 15px;
            font-weight: 400;
        }

        .participant-name {
            font-size: 42px;
            font-weight: 700;
            color: #B22234;
            margin: 20px 0;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .event-details {
            background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
            border: 3px solid #B22234;
            border-radius: 15px;
            padding: 25px 40px;
            margin: 30px auto;
            max-width: 700px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        }

        .event-title {
            font-size: 24px;
            font-weight: 700;
            color: #333;
            margin-bottom: 15px;
        }

        .event-info {
            font-size: 16px;
            color: #666;
            line-height: 2;
        }

        .qr-section {
            position: absolute;
            bottom: 45px;
            left: 45px;
            text-align: center;
        }

        .qr-placeholder {
            width: 100px;
            height: 100px;
            background: #f0f0f0;
            border: 3px dashed #ccc;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            color: #999;
            border-radius: 8px;
        }

        .verification-code {
            font-size: 10px;
            color: #666;
            margin-top: 6px;
            font-weight: 500;
        }

        .signature-section {
            position: absolute;
            bottom: 45px;
            right: 45px;
            text-align: center;
            max-width: 240px;
        }

        .signature-label {
            font-size: 13px;
            color: #666;
            margin-bottom: 12px;
            font-weight: 500;
        }

        .signature-image {
            max-width: 180px;
            max-height: 70px;
            object-fit: contain;
            margin: 12px 0;
        }

        .signature-placeholder {
            width: 180px;
            height: 60px;
            background: #f0f0f0;
            border: 3px dashed #ccc;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            color: #999;
            margin: 12px auto;
            border-radius: 8px;
        }

        .signature-line {
            width: 200px;
            height: 2px;
            background: #333;
            margin: 8px auto;
        }

        .organizer-name {
            font-size: 16px;
            font-weight: 600;
            color: #333;
            margin-top: 8px;
        }

        .issue-date {
            font-size: 13px;
            color: #999;
            margin-top: 8px;
        }

        .preview-watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 100px;
            font-weight: 700;
            color: rgba(178, 34, 52, 0.03);
            pointer-events: none;
            z-index: 1;
            letter-spacing: 10px;
        }

        /* Print Styles for PDF Download */
        @media print {
            body {
                background: white;
                padding: 0;
                margin: 0;
            }

            .a4-paper {
                width: 297mm;
                height: 210mm;
                box-shadow: none;
                transform: none;
                margin: 0;
                page-break-after: avoid;
            }

            .certificate-container {
                border: 10px solid #B22234;
                padding: 30px 45px;
            }

            /* Hide preview watermark when printing */
            .preview-watermark {
                display: none;
            }

            /* Ensure colors are printed */
            * {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
                color-adjust: exact;
            }
        }
    </style>
</head>
<body>
    <div class="a4-paper">
        <div class="certificate-container">
            <div class="preview-watermark">PREVIEW</div>

            <!-- Organizer Logo -->
            @if($sampleData->organizer_logo)
                <img src="{{ $sampleData->organizer_logo }}" alt="Organizer Logo" class="organizer-logo">
            @else
                <div style="position: absolute; top: 30px; right: 45px; width: 130px; height: 85px; background: #f0f0f0; border: 3px dashed #ccc; display: flex; align-items: center; justify-content: center; font-size: 13px; color: #999; border-radius: 8px;">
                    Logo Here
                </div>
            @endif

        <!-- Header -->
        <div class="certificate-header">
            <div class="platform-title">EVENT CONNECT</div>
            <div class="certificate-title">Certificate of Participation</div>
        </div>

        <!-- Body -->
        <div class="certificate-body">
            <div class="label">This certificate is proudly presented to</div>

            <div class="participant-name">{{ $sampleData->participant_name }}</div>

            <div class="label">For successfully participating in</div>

            <div class="event-details">
                <div class="event-title">{{ $sampleData->event_title }}</div>
                <div class="event-info">
                    <strong>Date:</strong> {{ $sampleData->event_date }}<br>
                    <strong>Organized by:</strong> {{ $sampleData->organizer_name }}
                </div>
            </div>
        </div>

        <!-- QR Code Section -->
        <div class="qr-section">
            <div class="qr-placeholder">QR Code</div>
            <div class="verification-code">CERT-SAMPLE-123</div>
        </div>

        <!-- Signature Section -->
        <div class="signature-section">
            <div class="signature-label">Authorized Signature</div>

            @if($sampleData->organizer_signature)
                <img src="{{ $sampleData->organizer_signature }}" alt="Signature" class="signature-image">
            @else
                <div class="signature-placeholder">Signature Here</div>
            @endif

            <div class="signature-line"></div>
            <div class="organizer-name">{{ $sampleData->organizer_name }}</div>
            <div class="signature-label">Event Organizer</div>
            <div class="issue-date">Issued: {{ $sampleData->event_date }}</div>
        </div>
        </div>
    </div>
</body>
</html>
