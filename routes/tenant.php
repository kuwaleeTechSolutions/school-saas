<?php

use App\Livewire\Tenant\Auth\Login as TenantLogin;
use App\Livewire\Tenant\Dashboard;
use App\Livewire\Tenant\Onboarding\Wizard as OnboardingWizard;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

$suffix = ltrim(config('platform.tenant_domain_suffix', '.localhost'), '.');

$registerNamed = function () {
    Route::middleware('guest')->group(function () {
        Route::get('/login', TenantLogin::class)->name('tenant.login');
    });

    Route::middleware('auth')->group(function () {
        Route::post('/logout', function () {
            Auth::logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();
            return redirect()->route('tenant.login');
        })->name('tenant.logout');

        Route::get('/billing/locked', fn () => view('tenant.billing-locked'))->name('tenant.billing.locked');
        Route::get('/billing/plans', fn () => view('tenant.billing-plans'))->name('tenant.billing.plans');
        Route::get('/billing/upgrade', fn () => view('tenant.billing-plans'))->name('tenant.billing.upgrade');

        Route::middleware('subscription.active')->group(function () {
            Route::get('/dashboard', Dashboard::class)->name('tenant.dashboard');
            Route::get('/onboarding', OnboardingWizard::class)->name('tenant.onboarding');

            // Stage 2
            Route::get('/students', \App\Livewire\Tenant\Student\Index::class)->name('tenant.students.index');
            Route::get('/students/create', \App\Livewire\Tenant\Student\Create::class)->name('tenant.students.create');
            Route::get('/students/{student}', \App\Livewire\Tenant\Student\Show::class)->name('tenant.students.show');
            Route::get('/students/{student}/edit', \App\Livewire\Tenant\Student\Edit::class)->name('tenant.students.edit');
            Route::get('/teachers', \App\Livewire\Tenant\Teacher\Index::class)->name('tenant.teachers.index');
            Route::get('/teachers/create', \App\Livewire\Tenant\Teacher\Create::class)->name('tenant.teachers.create');
            Route::get('/teachers/{teacher}/edit', \App\Livewire\Tenant\Teacher\Edit::class)->name('tenant.teachers.edit');
            Route::get('/guardians', \App\Livewire\Tenant\Guardian\Index::class)->name('tenant.guardians.index');
            Route::get('/guardians/create', \App\Livewire\Tenant\Guardian\Create::class)->name('tenant.guardians.create');
            Route::get('/academics', \App\Livewire\Tenant\Academics\Manage::class)->name('tenant.academics.manage');
            Route::get('/users', \App\Livewire\Tenant\Users\Index::class)->name('tenant.users.index');
            Route::get('/roles', \App\Livewire\Tenant\Roles\Index::class)->name('tenant.roles.index');
            Route::get('/roles/create', \App\Livewire\Tenant\Roles\Create::class)->name('tenant.roles.create');

            // Stage 3
            Route::get('/admissions', \App\Livewire\Tenant\Admissions\Index::class)->name('tenant.admissions.index');
            Route::get('/admissions/create', \App\Livewire\Tenant\Admissions\Create::class)->name('tenant.admissions.create');
            Route::get('/admissions/{application}', \App\Livewire\Tenant\Admissions\Show::class)->name('tenant.admissions.show');
            Route::get('/academic-sessions', \App\Livewire\Tenant\AcademicSessions\Index::class)->name('tenant.academic-sessions.index');
            Route::get('/academic-sessions/create', \App\Livewire\Tenant\AcademicSessions\Create::class)->name('tenant.academic-sessions.create');
            Route::get('/promotion', \App\Livewire\Tenant\Promotion\Wizard::class)->name('tenant.promotion.wizard');
            Route::get('/attendance', \App\Livewire\Tenant\Attendance\Take::class)->name('tenant.attendance.take');
            Route::get('/attendance/report', \App\Livewire\Tenant\Attendance\Report::class)->name('tenant.attendance.report');

            // Stage 4
            Route::get('/timetable', \App\Livewire\Tenant\Timetable\Manage::class)->name('tenant.timetable.manage');
            Route::get('/homework', \App\Livewire\Tenant\Homework\Index::class)->name('tenant.homework.index');
            Route::get('/homework/create', \App\Livewire\Tenant\Homework\Create::class)->name('tenant.homework.create');
            Route::get('/homework/{homework}', \App\Livewire\Tenant\Homework\Show::class)->name('tenant.homework.show');
            Route::get('/assignments', \App\Livewire\Tenant\Assignments\Index::class)->name('tenant.assignments.index');
            Route::get('/assignments/create', \App\Livewire\Tenant\Assignments\Create::class)->name('tenant.assignments.create');
            Route::get('/assignments/{assignment}', \App\Livewire\Tenant\Assignments\Show::class)->name('tenant.assignments.show');
            Route::get('/examinations', \App\Livewire\Tenant\Examinations\Index::class)->name('tenant.examinations.index');
            Route::get('/examinations/create', \App\Livewire\Tenant\Examinations\Create::class)->name('tenant.examinations.create');
            Route::get('/results/marks-entry/{exam}', \App\Livewire\Tenant\Results\MarksEntry::class)->name('tenant.results.marks-entry');
            Route::get('/results/approvals', \App\Livewire\Tenant\Results\ApprovalQueue::class)->name('tenant.results.approvals');
            Route::get('/report-cards', \App\Livewire\Tenant\ReportCards\Generate::class)->name('tenant.report-cards.generate');
            Route::get('/report-cards/{reportCard}', \App\Livewire\Tenant\ReportCards\Show::class)->name('tenant.report-cards.show');
            Route::get('/report-cards/{reportCard}/download', function (\App\Domain\Tenant\Models\ReportCard $reportCard) {
                abort_unless($reportCard->pdf_path && \Illuminate\Support\Facades\Storage::disk('local')->exists($reportCard->pdf_path), 404);
                return \Illuminate\Support\Facades\Storage::disk('local')->download($reportCard->pdf_path);
            })->name('tenant.report-cards.download');

            // Stage 5
            Route::get('/fees/structures', \App\Livewire\Tenant\Fees\Structures\Index::class)->name('tenant.fees.structures.index');
            Route::get('/fees/structures/create', \App\Livewire\Tenant\Fees\Structures\Create::class)->name('tenant.fees.structures.create');
            Route::get('/fees/invoices', \App\Livewire\Tenant\Fees\Invoices\Index::class)->name('tenant.fees.invoices.index');
            Route::get('/fees/invoices/generate', \App\Livewire\Tenant\Fees\Invoices\Generate::class)->name('tenant.fees.invoices.generate');
            Route::get('/fees/invoices/{invoice}', \App\Livewire\Tenant\Fees\Invoices\Show::class)->name('tenant.fees.invoices.show');
            Route::get('/fees/refunds', \App\Livewire\Tenant\Fees\Refunds\Index::class)->name('tenant.fees.refunds.index');
            Route::get('/fees/dashboard', \App\Livewire\Tenant\Fees\Reports\Dashboard::class)->name('tenant.fees.reports.dashboard');
            Route::get('/fees/receipts/{receipt}/download', function (\App\Domain\Tenant\Models\PaymentReceipt $receipt) {
                abort_unless($receipt->pdf_path && \Illuminate\Support\Facades\Storage::disk('local')->exists($receipt->pdf_path), 404);
                return \Illuminate\Support\Facades\Storage::disk('local')->download($receipt->pdf_path);
            })->name('tenant.fees.receipts.download');
            // Stage 6
            Route::get('/announcements', \App\Livewire\Tenant\Announcements\Index::class)->name('tenant.announcements.index');
            Route::get('/announcements/create', \App\Livewire\Tenant\Announcements\Create::class)->name('tenant.announcements.create');
            Route::get('/notifications', \App\Livewire\Tenant\Notifications\Index::class)->name('tenant.notifications.index');
            Route::get('/parent-portal', \App\Livewire\Tenant\ParentPortal\Dashboard::class)->name('tenant.parent-portal.dashboard');
            Route::get('/teacher-portal', \App\Livewire\Tenant\TeacherPortal\Dashboard::class)->name('tenant.teacher-portal.dashboard');

                        // Stage 7 - Library
            Route::get('/library/books', \App\Livewire\Tenant\Library\Books\Index::class)->name('tenant.library.books.index');
            Route::get('/library/books/create', \App\Livewire\Tenant\Library\Books\Create::class)->name('tenant.library.books.create');
            Route::get('/library/circulation', \App\Livewire\Tenant\Library\Circulation\Index::class)->name('tenant.library.circulation.index');

            // Transport
            Route::get('/transport/routes', \App\Livewire\Tenant\Transport\Routes\Index::class)->name('tenant.transport.routes.index');
            Route::get('/transport/assignments', \App\Livewire\Tenant\Transport\Assignments\Index::class)->name('tenant.transport.assignments.index');

            // Inventory
            Route::get('/inventory/items', \App\Livewire\Tenant\Inventory\Items\Index::class)->name('tenant.inventory.items.index');
            Route::get('/inventory/stock', \App\Livewire\Tenant\Inventory\Stock\Index::class)->name('tenant.inventory.stock.index');

            // HR / Leave / Payroll
            Route::get('/hr/staff', \App\Livewire\Tenant\Hr\Staff\Index::class)->name('tenant.hr.staff.index');
            Route::get('/hr/staff/create', \App\Livewire\Tenant\Hr\Staff\Create::class)->name('tenant.hr.staff.create');
            Route::get('/hr/leave', \App\Livewire\Tenant\Hr\Leave\Index::class)->name('tenant.hr.leave.index');
            Route::get('/hr/payroll', \App\Livewire\Tenant\Hr\Payroll\Index::class)->name('tenant.hr.payroll.index');

            Route::get('/hr/payroll/payslip/{payslip}/download', function (\App\Domain\Tenant\Models\Payslip $payslip) {
                abort_unless(
                    $payslip->pdf_path &&
                    \Illuminate\Support\Facades\Storage::disk('local')->exists($payslip->pdf_path),
                    404
                );

                return \Illuminate\Support\Facades\Storage::disk('local')->download($payslip->pdf_path);
            })->name('tenant.hr.payroll.payslip-download');

            Route::get('/hr/payroll/{run}', \App\Livewire\Tenant\Hr\Payroll\Show::class)->name('tenant.hr.payroll.show');

            // Visitor Management
            Route::get('/visitor-management', \App\Livewire\Tenant\VisitorManagement\Index::class)->name('tenant.visitor-management.index');

            // Complaints
            Route::get('/complaints', \App\Livewire\Tenant\Complaints\Index::class)->name('tenant.complaints.index');

            // Documents
            Route::get('/documents', \App\Livewire\Tenant\Documents\Index::class)->name('tenant.documents.index');

            Route::get('/documents/{document}/download', function (\App\Domain\Tenant\Models\SchoolDocument $document) {
                abort_unless(
                    \Illuminate\Support\Facades\Storage::disk('local')->exists($document->file_path),
                    404
                );

                return \Illuminate\Support\Facades\Storage::disk('local')
                    ->download($document->file_path, $document->original_name);
            })->name('tenant.documents.download');

            // Certificates
            Route::get('/certificates/templates', \App\Livewire\Tenant\Certificates\Templates::class)->name('tenant.certificates.templates');
            Route::get('/certificates/issue', \App\Livewire\Tenant\Certificates\Issue::class)->name('tenant.certificates.issue');

            Route::get('/certificates/{certificate}/download', function (\App\Domain\Tenant\Models\IssuedCertificate $certificate) {
                abort_unless(
                    $certificate->pdf_path &&
                    \Illuminate\Support\Facades\Storage::disk('local')->exists($certificate->pdf_path),
                    404
                );

                return \Illuminate\Support\Facades\Storage::disk('local')->download($certificate->pdf_path);
            })->name('tenant.certificates.download');
            // Stage 8 - Platform Billing
            Route::get('/billing', \App\Livewire\Tenant\Billing\Dashboard::class)
            ->name('tenant.billing.dashboard');
            // Stage 9 - Reports & Analytics

            Route::get('/reports', \App\Livewire\Tenant\Reports\Index::class)
            ->name('tenant.reports.index');

            Route::get(
            '/reports/{key}/export/{format}',
            \App\Http\Controllers\ReportExportController::class
            )->name('tenant.reports.export');

            Route::get('/analytics', \App\Livewire\Tenant\Analytics\Dashboard::class)
            ->name('tenant.analytics.dashboard');

        });
    });
};

$registerUnnamed = function () {
    Route::middleware('guest')->group(function () {
        Route::get('/login', TenantLogin::class);
    });

    Route::middleware('auth')->group(function () {
        Route::post('/logout', function () {
            Auth::logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();
            return redirect('login');
        });

        Route::get('/billing/locked', fn () => view('tenant.billing-locked'));
        Route::get('/billing/plans', fn () => view('tenant.billing-plans'));

        Route::middleware('subscription.active')->group(function () {
            Route::get('/dashboard', Dashboard::class);
            Route::get('/onboarding', OnboardingWizard::class);
            Route::get('/students', \App\Livewire\Tenant\Student\Index::class);
            Route::get('/students/create', \App\Livewire\Tenant\Student\Create::class);
            Route::get('/students/{student}', \App\Livewire\Tenant\Student\Show::class);
            Route::get('/students/{student}/edit', \App\Livewire\Tenant\Student\Edit::class);
            Route::get('/teachers', \App\Livewire\Tenant\Teacher\Index::class);
            Route::get('/teachers/create', \App\Livewire\Tenant\Teacher\Create::class);
            Route::get('/teachers/{teacher}/edit', \App\Livewire\Tenant\Teacher\Edit::class);
            Route::get('/guardians', \App\Livewire\Tenant\Guardian\Index::class);
            Route::get('/guardians/create', \App\Livewire\Tenant\Guardian\Create::class);
            Route::get('/academics', \App\Livewire\Tenant\Academics\Manage::class);
            Route::get('/users', \App\Livewire\Tenant\Users\Index::class);
            Route::get('/roles', \App\Livewire\Tenant\Roles\Index::class);
            Route::get('/roles/create', \App\Livewire\Tenant\Roles\Create::class);
            Route::get('/admissions', \App\Livewire\Tenant\Admissions\Index::class);
            Route::get('/admissions/create', \App\Livewire\Tenant\Admissions\Create::class);
            Route::get('/admissions/{application}', \App\Livewire\Tenant\Admissions\Show::class);
            Route::get('/academic-sessions', \App\Livewire\Tenant\AcademicSessions\Index::class);
            Route::get('/academic-sessions/create', \App\Livewire\Tenant\AcademicSessions\Create::class);
            Route::get('/promotion', \App\Livewire\Tenant\Promotion\Wizard::class);
            Route::get('/attendance', \App\Livewire\Tenant\Attendance\Take::class);
            Route::get('/attendance/report', \App\Livewire\Tenant\Attendance\Report::class);
            Route::get('/timetable', \App\Livewire\Tenant\Timetable\Manage::class);
            Route::get('/homework', \App\Livewire\Tenant\Homework\Index::class);
            Route::get('/homework/create', \App\Livewire\Tenant\Homework\Create::class);
            Route::get('/homework/{homework}', \App\Livewire\Tenant\Homework\Show::class);
            Route::get('/assignments', \App\Livewire\Tenant\Assignments\Index::class);
            Route::get('/assignments/create', \App\Livewire\Tenant\Assignments\Create::class);
            Route::get('/assignments/{assignment}', \App\Livewire\Tenant\Assignments\Show::class);
            Route::get('/examinations', \App\Livewire\Tenant\Examinations\Index::class);
            Route::get('/examinations/create', \App\Livewire\Tenant\Examinations\Create::class);
            Route::get('/results/marks-entry/{exam}', \App\Livewire\Tenant\Results\MarksEntry::class);
            Route::get('/results/approvals', \App\Livewire\Tenant\Results\ApprovalQueue::class);
            Route::get('/report-cards', \App\Livewire\Tenant\ReportCards\Generate::class);
            Route::get('/report-cards/{reportCard}', \App\Livewire\Tenant\ReportCards\Show::class);
            Route::get('/report-cards/{reportCard}/download', function (\App\Domain\Tenant\Models\ReportCard $reportCard) {
                abort_unless($reportCard->pdf_path && \Illuminate\Support\Facades\Storage::disk('local')->exists($reportCard->pdf_path), 404);
                return \Illuminate\Support\Facades\Storage::disk('local')->download($reportCard->pdf_path);
            });
            Route::get('/fees/structures', \App\Livewire\Tenant\Fees\Structures\Index::class);
            Route::get('/fees/structures/create', \App\Livewire\Tenant\Fees\Structures\Create::class);
            Route::get('/fees/invoices', \App\Livewire\Tenant\Fees\Invoices\Index::class);
            Route::get('/fees/invoices/generate', \App\Livewire\Tenant\Fees\Invoices\Generate::class);
            Route::get('/fees/invoices/{invoice}', \App\Livewire\Tenant\Fees\Invoices\Show::class);
            Route::get('/fees/refunds', \App\Livewire\Tenant\Fees\Refunds\Index::class);
            Route::get('/fees/dashboard', \App\Livewire\Tenant\Fees\Reports\Dashboard::class);
            Route::get('/fees/receipts/{receipt}/download', function (\App\Domain\Tenant\Models\PaymentReceipt $receipt) {
                abort_unless($receipt->pdf_path && \Illuminate\Support\Facades\Storage::disk('local')->exists($receipt->pdf_path), 404);
                return \Illuminate\Support\Facades\Storage::disk('local')->download($receipt->pdf_path);
            });
            // Stage 6
            Route::get('/announcements', \App\Livewire\Tenant\Announcements\Index::class);
            Route::get('/announcements/create', \App\Livewire\Tenant\Announcements\Create::class);
            Route::get('/notifications', \App\Livewire\Tenant\Notifications\Index::class);
            Route::get('/parent-portal', \App\Livewire\Tenant\ParentPortal\Dashboard::class);
            Route::get('/teacher-portal', \App\Livewire\Tenant\TeacherPortal\Dashboard::class);

                        // Stage 7 - Library
            Route::get('/library/books', \App\Livewire\Tenant\Library\Books\Index::class);
            Route::get('/library/books/create', \App\Livewire\Tenant\Library\Books\Create::class);
            Route::get('/library/circulation', \App\Livewire\Tenant\Library\Circulation\Index::class);


            // Stage 7 - Transport
            Route::get('/transport/routes', \App\Livewire\Tenant\Transport\Routes\Index::class);
            Route::get('/transport/assignments', \App\Livewire\Tenant\Transport\Assignments\Index::class);


            // Stage 7 - Inventory
            Route::get('/inventory/items', \App\Livewire\Tenant\Inventory\Items\Index::class);
            Route::get('/inventory/stock', \App\Livewire\Tenant\Inventory\Stock\Index::class);


            // Stage 7 - HR / Leave / Payroll
            Route::get('/hr/staff', \App\Livewire\Tenant\Hr\Staff\Index::class);
            Route::get('/hr/staff/create', \App\Livewire\Tenant\Hr\Staff\Create::class);
            Route::get('/hr/leave', \App\Livewire\Tenant\Hr\Leave\Index::class);
            Route::get('/hr/payroll', \App\Livewire\Tenant\Hr\Payroll\Index::class);

            Route::get(
                '/hr/payroll/payslip/{payslip}/download',
                function (\App\Domain\Tenant\Models\Payslip $payslip) {
                    abort_unless(
                        $payslip->pdf_path &&
                        \Illuminate\Support\Facades\Storage::disk('local')->exists($payslip->pdf_path),
                        404
                    );

                    return \Illuminate\Support\Facades\Storage::disk('local')
                        ->download($payslip->pdf_path);
                }
            );

            Route::get('/hr/payroll/{run}', \App\Livewire\Tenant\Hr\Payroll\Show::class);


            // Stage 7 - Visitor Management
            Route::get('/visitor-management', \App\Livewire\Tenant\VisitorManagement\Index::class);


            // Stage 7 - Complaints
            Route::get('/complaints', \App\Livewire\Tenant\Complaints\Index::class);


            // Stage 7 - Documents
            Route::get('/documents', \App\Livewire\Tenant\Documents\Index::class);

            Route::get(
                '/documents/{document}/download',
                function (\App\Domain\Tenant\Models\SchoolDocument $document) {
                    abort_unless(
                        \Illuminate\Support\Facades\Storage::disk('local')
                            ->exists($document->file_path),
                        404
                    );

                    return \Illuminate\Support\Facades\Storage::disk('local')
                        ->download(
                            $document->file_path,
                            $document->original_name
                        );
                }
            );


            // Stage 7 - Certificates
            Route::get('/certificates/templates', \App\Livewire\Tenant\Certificates\Templates::class);
            Route::get('/certificates/issue', \App\Livewire\Tenant\Certificates\Issue::class);

            Route::get(
                '/certificates/{certificate}/download',
                function (\App\Domain\Tenant\Models\IssuedCertificate $certificate) {
                    abort_unless(
                        $certificate->pdf_path &&
                        \Illuminate\Support\Facades\Storage::disk('local')->exists($certificate->pdf_path),
                        404
                    );

                    return \Illuminate\Support\Facades\Storage::disk('local')
                        ->download($certificate->pdf_path);
                }
            );
            // Stage 8 - Platform Billing
            Route::get('/billing', \App\Livewire\Tenant\Billing\Dashboard::class);
            // Stage 9 - Reports & Analytics

            Route::get('/reports', \App\Livewire\Tenant\Reports\Index::class);

            Route::get(
               '/reports/{key}/export/{format}',
                \App\Http\Controllers\ReportExportController::class
            );

            Route::get('/analytics', \App\Livewire\Tenant\Analytics\Dashboard::class);
        });
    });
};

Route::domain('{school_slug}.' . $suffix)
    ->middleware(['web', 'tenant'])
    ->group($registerNamed);

Route::prefix('school/{school_slug}')
    ->middleware(['web', 'tenant'])
    ->group($registerUnnamed);
