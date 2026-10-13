<?php
namespace Database\Factories;
use App\Domain\Tenant\Models\School;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
class UserFactory extends Factory {
    protected $model = User::class;
    protected static ?string $password;
    public function definition(): array {
        return ['name' => $this->faker->name(), 'email' => $this->faker->unique()->safeEmail(), 'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'), 'status' => 'active', 'remember_token' => \Illuminate\Support\Str::random(10)];
    }
    public function forSchool(School $school): static { return $this->state(fn() => ['school_id' => $school->id]); }
    public function unverified(): static { return $this->state(fn() => ['email_verified_at' => null]); }
}
