<?php
namespace App\Domain\Tenant\Reports\Definitions;
use App\Domain\Tenant\Models\Section;
use App\Domain\Tenant\Models\Student;
use App\Domain\Tenant\Reports\ReportDefinition;
use Illuminate\Support\Collection;
/** Deliberately omits phone, address and medical fields (Section 98 privacy). */
class StudentListReport implements ReportDefinition
{
    public function key(): string { return 'students'; }
    public function title(): string { return 'Student List'; }
    public function permission(): string { return 'students.export'; }
    public function filters(): array
    {
        $sections = Section::with('schoolClass')->orderBy('class_id')->orderBy('name')->get()->mapWithKeys(fn ($s) => [(string) $s->id => $s->schoolClass->name.' - '.$s->name])->all();
        return [
            'section_id' => ['label' => 'Class / Section', 'type' => 'select', 'options' => $sections],
            'status' => ['label' => 'Status', 'type' => 'select', 'options' => ['active' => 'Active', 'inactive' => 'Inactive', 'alumni' => 'Alumni']],
        ];
    }
    public function columns(): array { return ['admission_number' => 'Admission #', 'name' => 'Name', 'class_section' => 'Class / Section', 'gender' => 'Gender', 'status' => 'Status']; }
    public function rules(array $input = []): array { return ['section_id' => ['nullable', 'integer'], 'status' => ['nullable', 'in:active,inactive,alumni']]; }
    public function rows(array $filters, int $limit): Collection
    {
        return Student::query()->with(['currentEnrollment.schoolClass', 'currentEnrollment.section'])
            ->when($filters['status'] ?? null, fn ($q, $v) => $q->where('status', $v))
            ->when($filters['section_id'] ?? null, fn ($q, $v) => $q->whereHas('enrollments', fn ($e) => $e->where('section_id', $v)->where('status', 'active')))
            ->orderBy('first_name')->limit($limit)->get()
            ->map(fn ($s) => [
                'admission_number' => $s->admission_number, 'name' => $s->fullName(),
                'class_section' => $s->currentEnrollment ? $s->currentEnrollment->schoolClass->name.' - '.$s->currentEnrollment->section->name : '',
                'gender' => $s->gender, 'status' => $s->status,
            ]);
    }
}
