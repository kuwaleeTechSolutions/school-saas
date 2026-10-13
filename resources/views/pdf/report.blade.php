<!DOCTYPE html><html><head><meta charset="utf-8"><style>
body{font-family:Helvetica,Arial,sans-serif;font-size:10px;color:#1e293b}h1{font-size:14px}table{width:100%;border-collapse:collapse}th,td{border:1px solid #cbd5e1;padding:4px 6px;text-align:left}th{background:#f1f5f9}
</style></head><body>
<h1>{{ $result->definition->title() }}</h1><p>Generated {{ now()->format('d M Y H:i') }}@if ($result->truncated) &middot; first {{ count($result->rows) }} rows only @endif</p>
<table><thead><tr>@foreach ($result->definition->columns() as $h)<th>{{ $h }}</th>@endforeach</tr></thead>
<tbody>@foreach ($result->rows as $row)<tr>@foreach (array_keys($result->definition->columns()) as $c)<td>{{ $row[$c] ?? '' }}</td>@endforeach</tr>@endforeach</tbody></table>
</body></html>
