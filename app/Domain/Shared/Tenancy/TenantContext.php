<?php
namespace App\Domain\Shared\Tenancy;
use App\Domain\Tenant\Models\School;
use RuntimeException;

class TenantContext
{
    protected ?School $school = null;
    public function set(School $school): void { $this->school = $school; }
    public function clear(): void { $this->school = null; }
    public function hasSchool(): bool { return $this->school !== null; }
    public function school(): School
    {
        if (! $this->school) throw new RuntimeException('No tenant is bound to the current request. Did IdentifyTenant middleware run?');
        return $this->school;
    }
    public function currentSchoolId(): ?int { return $this->school?->id; }
}
