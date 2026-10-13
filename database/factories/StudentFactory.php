<?php
namespace Database\Factories;
use App\Domain\Tenant\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
class StudentFactory extends Factory {
    protected $model = Student::class;
    public function definition(): array {
        return ['ulid' => (string) Str::ulid(), 'admission_number' => 'ADM-' . $this->faker->unique()->numerify('#####'),
            'first_name' => $this->faker->firstName(), 'last_name' => $this->faker->lastName(), 'admission_date' => now(), 'status' => 'active'];
    }
}
