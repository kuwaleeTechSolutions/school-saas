<?php
namespace Database\Factories;
use App\Domain\Tenant\Models\SchoolClass;
use Illuminate\Database\Eloquent\Factories\Factory;
class SchoolClassFactory extends Factory {
    protected $model = SchoolClass::class;
    public function definition(): array { return ['name' => 'Grade ' . $this->faker->numberBetween(1, 12), 'sort_order' => 0]; }
}
