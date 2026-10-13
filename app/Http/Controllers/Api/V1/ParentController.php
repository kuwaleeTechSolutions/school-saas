<?php
namespace App\Http\Controllers\Api\V1;
use App\Domain\Tenant\Models\Guardian;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
class ParentController extends \App\Http\Controllers\Controller
{
    /** Scope is the shape of the query: starts from the caller's own Guardian row (own_children_only). */
    public function children(Request $request): JsonResponse
    {
        $guardian = Guardian::where('user_id', $request->user()->id)->first();
        if (! $guardian) return response()->json(['data' => []]);
        $students = $guardian->students()->with(['currentEnrollment.schoolClass', 'currentEnrollment.section'])->get();
        return response()->json(['data' => $students->map(fn ($s) => StaffStudentController::present($s))->values()]);
    }
}
