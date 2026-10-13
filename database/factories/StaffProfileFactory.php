<?php

namespace Database\Factories;

use App\Domain\Tenant\Models\StaffProfile;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class StaffProfileFactory extends Factory
{
    protected $model = StaffProfile::class;

    public function definition(): array
    {
        return [
            'ulid' => (string) Str::ulid(),
            'employee_code' => 'EMP-' . $this->faker->unique()->numerify('#####'),
            'designation' => 'Staff',
            'joining_date' => now(),
            'monthly_gross_salary' => 30000,
            'annual_leave_quota' => 12,
            'status' => 'active',
        ];
    }
}
