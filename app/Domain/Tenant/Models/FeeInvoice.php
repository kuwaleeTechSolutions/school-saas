<?php
namespace App\Domain\Tenant\Models;
use App\Domain\Shared\Tenancy\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
class FeeInvoice extends Model {
    use BelongsToSchool, SoftDeletes;
    protected $fillable = ['school_id','student_id','academic_session_id','fee_installment_id','invoice_number','due_date','subtotal_amount','discount_amount','late_fee_amount','tax_amount','total_amount','paid_amount','balance_amount','status'];
    protected function casts(): array { return ['due_date'=>'date','subtotal_amount'=>'decimal:2','discount_amount'=>'decimal:2','late_fee_amount'=>'decimal:2','tax_amount'=>'decimal:2','total_amount'=>'decimal:2','paid_amount'=>'decimal:2','balance_amount'=>'decimal:2']; }
    protected static function booted(): void { static::creating(fn($i) => $i->ulid ??= (string) Str::ulid()); }
    public function student(): BelongsTo { return $this->belongsTo(Student::class); }
    public function academicSession(): BelongsTo { return $this->belongsTo(AcademicSession::class); }
    public function installment(): BelongsTo { return $this->belongsTo(FeeInstallment::class, 'fee_installment_id'); }
    public function lineItems(): HasMany { return $this->hasMany(InvoiceLineItem::class); }
    public function payments(): HasMany { return $this->hasMany(FeePayment::class); }
    public function isOverdue(): bool { return in_array($this->status, ['unpaid','partially_paid'], true) && $this->due_date->isPast(); }
}
