<?php
namespace App\Http\Controllers;
use App\Domain\Tenant\Reports\ReportRunner;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use InvalidArgumentException;
class ReportExportController extends Controller
{
    public function __invoke(Request $request, ReportRunner $runner, string $key, string $format)
    {
        abort_unless(in_array($format, ['csv', 'pdf'], true), 404);
        try { $result = $runner->run($key, $request->query(), $request->user()); }
        catch (AuthorizationException) { abort(403); }
        catch (InvalidArgumentException) { abort(404); }
        $name = $key.'-'.now()->format('Ymd-His');
        if ($format === 'csv') return response()->streamDownload(fn () => print($runner->toCsv($result)), $name.'.csv', ['Content-Type' => 'text/csv']);
        return app('dompdf.wrapper')->loadView('pdf.report', ['result' => $result])->download($name.'.pdf');
    }
}
