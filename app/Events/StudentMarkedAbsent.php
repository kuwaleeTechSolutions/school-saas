<?php
namespace App\Events;
use App\Domain\Tenant\Models\AttendanceRecord;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
class StudentMarkedAbsent {
    use Dispatchable, SerializesModels;
    public function __construct(public AttendanceRecord $record) {}
}
