<?php
namespace Database\Factories;
use App\Domain\Tenant\Models\Exam;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
class ExamFactory extends Factory {
    protected $model = Exam::class;
    public function definition(): array {
        return ['ulid' => (string) Str::ulid(), 'name' => 'Unit Test 1', 'starts_on' => now()->subDays(10), 'ends_on' => now()->subDays(3), 'status' => 'draft'];
    }
}
