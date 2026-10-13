<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Helvetica, Arial, sans-serif; font-size: 12px; color: #1e293b; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { font-size: 16px; margin: 4px 0; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        th, td { border: 1px solid #cbd5e1; padding: 6px 8px; text-align: left; }
        th { background: #f1f5f9; }
        .meta-table td { border: none; padding: 3px 8px 3px 0; }
        .total-row td { font-weight: bold; }
        .signature { margin-top: 50px; width: 100%; }
        .signature td { border: none; text-align: center; padding-top: 30px; border-top: 1px solid #1e293b; width: 50%; }
        .qr-note { margin-top: 20px; font-size: 10px; color: #64748b; text-align: center; }
    </style>
</head>
<body>
    <div class="header"><h1>{{ $receipt->payment->invoice->student->school->name }}</h1><div>Fee Payment Receipt</div></div>
    <table class="meta-table">
        <tr><td><strong>Receipt No:</strong> {{ $receipt->receipt_number }}</td><td><strong>Date:</strong> {{ $receipt->payment->paid_at->format('d M Y') }}</td></tr>
        <tr><td><strong>Student:</strong> {{ $receipt->payment->invoice->student->fullName() }}</td><td><strong>Admission #:</strong> {{ $receipt->payment->invoice->student->admission_number }}</td></tr>
        <tr><td><strong>Invoice #:</strong> {{ $receipt->payment->invoice->invoice_number }}</td><td><strong>Payment Method:</strong> {{ str($receipt->payment->method)->headline() }}</td></tr>
        @if ($receipt->payment->transaction_reference)<tr><td colspan="2"><strong>Reference:</strong> {{ $receipt->payment->transaction_reference }}</td></tr>@endif
    </table>
    <table>
        <thead><tr><th>Description</th><th>Amount</th></tr></thead>
        <tbody>
            @foreach ($receipt->payment->invoice->lineItems as $item)<tr><td>{{ $item->description }}</td><td>{{ number_format($item->amount, 2) }}</td></tr>@endforeach
            <tr class="total-row"><td>Amount Paid</td><td>{{ number_format($receipt->payment->amount, 2) }}</td></tr>
        </tbody>
    </table>
    <table class="signature"><tr><td>Received By</td><td>Authorized Signature</td></tr></table>
    <div class="qr-note">Verification code: {{ $receipt->qr_token }}</div>
</body>
</html>
