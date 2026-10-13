<?php
namespace Database\Factories;
use App\Domain\Platform\Models\Coupon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
class CouponFactory extends Factory
{
    protected $model = Coupon::class;
    public function definition(): array
    {
        return ['ulid'=>(string) Str::ulid(),'code'=>strtoupper('TEST'.$this->faker->unique()->numerify('####')),'discount_type'=>'percentage','value'=>10,'max_redemptions_per_school'=>1,'is_active'=>true,'created_by'=>1];
    }
}
