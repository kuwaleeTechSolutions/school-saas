<?php
namespace Database\Factories;
use App\Domain\Tenant\Models\Section;
use Illuminate\Database\Eloquent\Factories\Factory;
class SectionFactory extends Factory {
    protected $model = Section::class;
    public function definition(): array { return ['name' => $this->faker->randomElement(['A','B','C']), 'sort_order' => 0]; }
}
