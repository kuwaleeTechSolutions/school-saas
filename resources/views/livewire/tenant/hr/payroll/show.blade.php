<div class="max-w-3xl">
    <h1 class="text-lg font-semibold text-slate-800 mb-6">{{ $run->label() }} Payroll</h1>
    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50"><tr><th class="px-4 py-3 text-left font-medium text-slate-600">Staff</th><th class="px-4 py-3 text-left font-medium text-slate-600">Gross</th><th class="px-4 py-3 text-left font-medium text-slate-600">Leave Deduction</th><th class="px-4 py-3 text-left font-medium text-slate-600">Net</th><th class="px-4 py-3 text-left font-medium text-slate-600">PDF</th></tr></thead>
            <tbody class="divide-y divide-slate-100">
                @foreach ($run->payslips as $payslip)
                    <tr>
                        <td class="px-4 py-3 font-medium text-slate-800">{{ $payslip->staffProfile->user->name }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ number_format($payslip->gross_salary, 2) }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ number_format($payslip->leave_deduction, 2) }} ({{ $payslip->unpaid_leave_days }}d)</td>
                        <td class="px-4 py-3 font-medium text-slate-800">{{ number_format($payslip->net_salary, 2) }}</td>
                        <td class="px-4 py-3">@if ($payslip->pdf_status === 'generated')<a href="{{ route('tenant.hr.payroll.payslip-download', $payslip) }}" class="text-teal-700 hover:underline">Download</a>@else<span class="text-slate-400">{{ str($payslip->pdf_status)->headline() }}</span>@endif</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
