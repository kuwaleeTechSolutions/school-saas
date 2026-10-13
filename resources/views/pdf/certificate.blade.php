<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Helvetica, Arial, sans-serif; font-size: 13px; color: #1e293b; padding: 40px; }
        .header { text-align: center; margin-bottom: 30px; }
        .header h1 { font-size: 18px; margin: 4px 0; }
        .certificate-title { text-align: center; font-size: 16px; font-weight: bold; margin: 20px 0; text-decoration: underline; }
        .body-text { line-height: 1.8; margin: 20px 0; }
        .footer { margin-top: 60px; display: flex; justify-content: space-between; }
        .qr-note { margin-top: 40px; font-size: 10px; color: #64748b; text-align: center; }
    </style>
</head>
<body>
    <div class="header"><h1>{{ $certificate->school->name }}</h1></div>
    <div class="certificate-title">{{ $certificate->template->name }}</div>
    <div style="text-align: right; font-size: 11px;">Certificate No: {{ $certificate->certificate_number }}</div>
    <div class="body-text">{{ $certificate->rendered_body }}</div>
    <div class="footer">
        <div>Date: {{ $certificate->issued_on->format('d M Y') }}</div>
        <div>Authorized Signature</div>
    </div>
    <div class="qr-note">Verification code: {{ $certificate->qr_token }}</div>
</body>
</html>
