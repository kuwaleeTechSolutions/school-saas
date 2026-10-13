<?php
namespace Database\Factories;
use App\Domain\Tenant\Models\AcademicSession;
use Illuminate\Database\Eloquent\Factories\Factory;
class AcademicSessionFactory extends Factory {
    protected $model = AcademicSession::class;
    public function definition(): array { return ['name' => '2026-27', 'starts_on' => '2026-04-01', 'ends_on' => '2027-03-31', 'is_current' => false]; }
    public function current(): static { return $this->state(fn() => ['is_current' => true]); }
}
