<?php
namespace App\Livewire\Tenant\Fees\Structures;
use App\Domain\Shared\Audit\AuditService;
use App\Domain\Shared\Tenancy\TenantContext;
use App\Domain\Tenant\Models\FeeComponent;
use App\Domain\Tenant\Models\FeeInstallment;
use App\Domain\Tenant\Models\FeeInstallmentPlan;
use App\Domain\Tenant\Models\FeeStructure;
use App\Domain\Tenant\Models\FeeStructureItem;
use App\Domain\Tenant\Models\SchoolClass;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Create extends Component
{
    public string $name = '';
    public ?int $classId = null;
    public ?int $sectionId = null;
    public string $studentCategory = '';
    public bool $requiresTransport = false;
    public bool $requiresHostel = false;
    public array $componentAmounts = [];
    public string $installmentFrequency = 'annual';
    public int $installmentCount = 1;

    public function mount(): void
    {
        $this->authorize('create', FeeStructure::class);
        $this->componentAmounts = FeeComponent::where('is_active', true)->get()->map(fn($c) => ['fee_component_id' => $c->id, 'name' => $c->name, 'annual_amount' => 0])->all();
    }

    public function updatedInstallmentFrequency(): void
    {
        $this->installmentCount = match ($this->installmentFrequency) { 'monthly' => 12, 'quarterly' => 4, 'half_yearly' => 2, 'annual' => 1, default => 1 };
    }

    public function save(TenantContext $tenant, AuditService $audit): void
    {
        $this->validate(['name' => ['required', 'string', 'max:255'], 'installmentCount' => ['required', 'integer', 'min:1', 'max:12']]);
        $school = $tenant->school();
        $session = $school->activeAcademicSession();
        if (! $session) { $this->addError('name', 'No active academic session.'); return; }

        DB::transaction(function () use ($school, $session, $audit) {
            $structure = FeeStructure::create([
                'academic_session_id' => $session->id, 'name' => $this->name, 'class_id' => $this->classId, 'section_id' => $this->sectionId,
                'student_category' => $this->studentCategory ?: null, 'requires_transport' => $this->requiresTransport,
                'requires_hostel' => $this->requiresHostel, 'is_active' => true,
            ]);
            foreach ($this->componentAmounts as $row) {
                if ((float) $row['annual_amount'] > 0) {
                    FeeStructureItem::create(['fee_structure_id' => $structure->id, 'fee_component_id' => $row['fee_component_id'], 'annual_amount' => $row['annual_amount']]);
                }
            }
            $plan = FeeInstallmentPlan::create(['fee_structure_id' => $structure->id, 'name' => ucfirst($this->installmentFrequency) . " ({$this->installmentCount} installments)", 'frequency' => $this->installmentFrequency]);
            $percentPerInstallment = round(100 / $this->installmentCount, 2);
            for ($i = 1; $i <= $this->installmentCount; $i++) {
                FeeInstallment::create([
                    'fee_installment_plan_id' => $plan->id, 'installment_number' => $i, 'label' => "Installment {$i}",
                    'percentage_of_total' => $i === $this->installmentCount ? round(100 - $percentPerInstallment * ($this->installmentCount - 1), 2) : $percentPerInstallment,
                    'due_date' => now()->addMonths($i - 1)->startOfMonth()->addDays(4),
                ]);
            }
            $audit->logSchool(auth()->id(), 'fee_structure.created', FeeStructure::class, $structure->id, null, ['name' => $structure->name]);
        });

        session()->flash('success', 'Fee structure created.');
        $this->redirect(route('tenant.fees.structures.index'), navigate: true);
    }

    public function render()
    {
        $school = app(TenantContext::class)->school();
        $session = $school->activeAcademicSession();
        return view('livewire.tenant.fees.structures.create', ['classes' => $session ? SchoolClass::where('academic_session_id', $session->id)->orderBy('sort_order')->get() : collect()]);
    }
}
