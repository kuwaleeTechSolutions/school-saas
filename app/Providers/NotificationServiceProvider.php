<?php

namespace App\Providers;

use App\Events\ExamResultsPublished;
use App\Events\FeePaymentRecorded;
use App\Events\StudentMarkedAbsent;
use App\Listeners\SendAbsenceNotification;
use App\Listeners\SendPaymentConfirmationNotification;
use App\Listeners\SendResultsPublishedNotification;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

/**
 * Explicit event->listener registration (consistent with this project's
 * preference for explicit wiring over relying on Laravel's listener
 * auto-discovery — see PolicyServiceProvider's own docblock from Stage 2).
 */
class NotificationServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Event::listen(StudentMarkedAbsent::class, SendAbsenceNotification::class);
        Event::listen(ExamResultsPublished::class, SendResultsPublishedNotification::class);
        Event::listen(FeePaymentRecorded::class, SendPaymentConfirmationNotification::class);
    }
}
