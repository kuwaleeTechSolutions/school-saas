<?php
namespace Database\Factories;
use App\Domain\Tenant\Models\School;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
class SchoolFactory extends Factory {
    protected $model = School::class;
    public function definition(): array {
        $name = $this->faker->unique()->company() . ' School';
        return ['school_code' => strtoupper(Str::random(8)), 'name' => $name, 'slug' => Str::slug($name) . '-' . Str::lower(Str::random(4)),
            'email' => $this->faker->unique()->safeEmail(), 'phone' => $this->faker->phoneNumber(), 'city' => $this->faker->city(),
            'state' => 'Assam', 'country' => 'India', 'status' => 'active', 'onboarding_status' => 'completed', 'timezone' => 'Asia/Kolkata', 'currency' => 'INR'];
    }
}
