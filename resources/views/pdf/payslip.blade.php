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
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $payslip->staffProfile->user->school->name ?? '' }}</h1>
        <div>Payslip &mdash; {{ $payslip->payrollRun->label() }}</div>
    </div>
    <table class="meta-table">
        <tr><td><strong>Employee:</strong> {{ $payslip->staffProfile->user->name }}</td><td><strong>Designation:</strong> {{ $payslip->staffProfile->designation ?? '—' }}</td></tr>
        <tr><td><strong>Employee Code:</strong> {{ $payslip->staffProfile->employee_code ?? '—' }}</td><td><strong>Department:</strong> {{ $payslip->staffProfile->department ?? '—' }}</td></tr>
    </table>
    <table>
        <thead><tr><th>Description</th><th>Amount</th></tr></thead>
        <tbody>
            <tr><td>Gross Salary</td><td>{{ number_format($payslip->gross_salary, 2) }}</td></tr>
            <tr><td>Leave Deduction ({{ $payslip->unpaid_leave_days }} unpaid days)</td><td>-{{ number_format($payslip->leave_deduction, 2) }}</td></tr>
            <tr><td>Other Deductions</td><td>-{{ number_format($payslip->other_deductions, 2) }}</td></tr>
            <tr class="total-row"><td>Net Salary</td><td>{{ number_format($payslip->net_salary, 2) }}</td></tr>
        </tbody>
    </table>
</body>
</html>
