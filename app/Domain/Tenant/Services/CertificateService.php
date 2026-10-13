<?php
namespace App\Domain\Tenant\Services;
use App\Domain\Shared\Audit\AuditService;
use App\Domain\Tenant\Models\CertificateTemplate;
use App\Domain\Tenant\Models\IssuedCertificate;
use App\Domain\Tenant\Models\Student;
use App\Jobs\GenerateCertificatePdf;
use App\Models\User;

class CertificateService
{
    public function __construct(protected AuditService $audit) {}

    public function issue(CertificateTemplate $template, Student $student, User $actor): IssuedCertificate
    {
        $enrollment = $student->currentEnrollment;
        $renderedBody = $template->render([
            'student_name' => $student->fullName(), 'admission_number' => $student->admission_number,
            'class_section' => $enrollment ? "{$enrollment->schoolClass->name} - {$enrollment->section->name}" : '',
            'date_of_birth' => $student->date_of_birth?->format('d M Y') ?? '', 'issue_date' => now()->format('d M Y'),
        ]);
        $certificate = IssuedCertificate::create([
            'certificate_template_id' => $template->id, 'student_id' => $student->id,
            'certificate_number' => $this->generateCertificateNumber($student), 'rendered_body' => $renderedBody,
            'issued_by' => $actor->id, 'issued_on' => now()->toDateString(), 'pdf_status' => 'queued',
        ]);
        GenerateCertificatePdf::dispatch($certificate->id);
        $this->audit->logSchool($actor->id, 'certificate.issued', IssuedCertificate::class, $certificate->id, null, ['student_id' => $student->id, 'template' => $template->name]);
        return $certificate->fresh();
    }

    public function verifyByToken(string $qrToken): ?IssuedCertificate
    {
        return IssuedCertificate::withoutGlobalScopes()->where('qr_token', $qrToken)->first();
    }

    protected function generateCertificateNumber(Student $student): string
    {
        $year = now()->format('y');
        $sequence = IssuedCertificate::where('school_id', $student->school_id)->count() + 1;
        return "CERT{$year}-" . str_pad((string) $sequence, 5, '0', STR_PAD_LEFT);
    }
}
