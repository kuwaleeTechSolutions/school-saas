<?php

namespace App\Listeners;

use App\Domain\Tenant\Models\Guardian;
use App\Domain\Tenant\Services\NotificationService;
use App\Events\FeePaymentRecorded;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendPaymentConfirmationNotification implements ShouldQueue
{
    public function __construct(protected NotificationService $notifications)
    {
    }

    public function handle(FeePaymentRecorded $event): void
    {
        $payment = $event->payment;
        $student = $payment->invoice->student;

        $guardians = Guardian::whereHas('students', fn ($q) => $q->where('students.id', $student->id))
            ->whereNotNull('user_id')
            ->with('user')
            ->get();

        foreach ($guardians as $guardian) {
            if (! $guardian->user) {
                continue;
            }

            $this->notifications->dispatch(
                $guardian->user,
                'fees.payment_received',
                'Payment Received: ' . number_format((float) $payment->amount, 2),
                "A payment of {$payment->amount} was recorded against invoice {$payment->invoice->invoice_number} for {$student->fullName()}.",
                ['payment_id' => $payment->id, 'invoice_id' => $payment->invoice->id],
                ['email'],
            );
        }
    }
}
