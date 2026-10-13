<?php
namespace App\Http\Controllers\Api\V1;
use App\Domain\Tenant\Models\Teacher;
use App\Domain\Tenant\Models\TeacherSubjectAssignment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
class TeacherController extends \App\Http\Controllers\Controller
{
    /** assigned_classes_only: starts from the caller's own Teacher row. */
    public function classes(Request $request): JsonResponse
    {
        $teacher = Teacher::where('user_id', $request->user()->id)->first();
        if (! $teacher) return response()->json(['data' => []]);
        $rows = TeacherSubjectAssignment::where('teacher_id', $teacher->id)->with(['schoolClass', 'section', 'subject'])->get()
            ->map(fn ($a) => ['class' => $a->schoolClass->name, 'section' => $a->section?->name, 'subject' => $a->subject->name])->values();
        return response()->json(['data' => $rows]);
    }
}
