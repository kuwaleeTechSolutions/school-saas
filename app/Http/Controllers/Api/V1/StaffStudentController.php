<?php
namespace App\Http\Controllers\Api\V1;
use App\Domain\Tenant\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
class StaffStudentController extends \App\Http\Controllers\Controller
{
    /** Same authorisation as the web UI (StudentPolicy -> plan AND role). Minimal fields only — no phone/address/medical. */
    public function index(): JsonResponse
    {
        Gate::authorize('viewAny', Student::class);
        $p = Student::with(['currentEnrollment.schoolClass', 'currentEnrollment.section'])->orderBy('first_name')->paginate(25);
        return response()->json(['data' => $p->getCollection()->map(fn ($s) => self::present($s))->values(), 'meta' => ['page' => $p->currentPage(), 'last_page' => $p->lastPage(), 'total' => $p->total()]]);
    }
    public static function present(Student $s): array
    {
        $e = $s->currentEnrollment;
        return ['id' => $s->ulid, 'admission_number' => $s->admission_number, 'name' => $s->fullName(), 'class_section' => $e ? $e->schoolClass->name.' - '.$e->section->name : null, 'status' => $s->status];
    }
}
