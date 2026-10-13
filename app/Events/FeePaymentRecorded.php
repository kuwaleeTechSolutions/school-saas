<?php

namespace App\Events;

use App\Domain\Tenant\Models\FeePayment;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/** Section 34/41 — fired once a payment is successfully recorded. */
class FeePaymentRecorded
{
    use Dispatchable, SerializesModels;

    public function __construct(public FeePayment $payment)
    {
    }
}
