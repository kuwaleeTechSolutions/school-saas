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
        .meta-table td { border: none; padding: 2px 8px 2px 0; }
        .remarks .box { border: 1px solid #cbd5e1; padding: 8px; margin-bottom: 10px; min-height: 40px; }
        .signatures { margin-top: 40px; width: 100%; }
        .signatures td { border: none; text-align: center; padding-top: 30px; border-top: 1px solid #1e293b; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $reportCard->school->name }}</h1>
        <div>Report Card &mdash; {{ $reportCard->snapshot['exam_name'] }}</div>
    </div>
    <table class="meta-table">
        <tr><td><strong>Student:</strong> {{ $reportCard->snapshot['student_name'] }}</td><td><strong>Admission #:</strong> {{ $reportCard->snapshot['admission_number'] }}</td></tr>
        <tr><td><strong>Class / Section:</strong> {{ $reportCard->snapshot['class_section'] }}</td><td><strong>Attendance:</strong> {{ $reportCard->snapshot['attendance_percent'] ?? '—' }}%</td></tr>
    </table>
    <table>
        <thead><tr><th>Subject</th><th>Max Marks</th><th>Marks Obtained</th><th>Percent</th><th>Grade</th><th>Result</th></tr></thead>
        <tbody>
            @foreach ($reportCard->snapshot['subjects'] as $row)
                <tr><td>{{ $row['subject'] }}</td><td>{{ number_format($row['max_marks'], 2) }}</td><td>{{ $row['is_absent'] ? 'Absent' : number_format($row['marks_obtained'], 2) }}</td><td>{{ $row['percent'] !== null ? $row['percent'] . '%' : '—' }}</td><td>{{ $row['grade'] ?? '—' }}</td><td>{{ $row['is_absent'] ? '—' : ($row['passed'] ? 'Pass' : 'Fail') }}</td></tr>
            @endforeach
        </tbody>
    </table>
    <div><strong>Total:</strong> {{ number_format($reportCard->snapshot['total_obtained'], 2) }} / {{ number_format($reportCard->snapshot['total_max'], 2) }} &nbsp;&middot;&nbsp; <strong>Overall:</strong> {{ $reportCard->overall_percent }}% &mdash; Grade {{ $reportCard->overall_grade ?? '—' }}</div>
    <div class="remarks">
        <div><strong>Teacher's Remarks</strong></div><div class="box">{{ $reportCard->teacher_remarks ?? '—' }}</div>
        <div><strong>Principal's Remarks</strong></div><div class="box">{{ $reportCard->principal_remarks ?? '—' }}</div>
    </div>
    <table class="signatures"><tr><td>Class Teacher</td><td>Principal</td><td>Parent / Guardian</td></tr></table>
</body>
</html>
