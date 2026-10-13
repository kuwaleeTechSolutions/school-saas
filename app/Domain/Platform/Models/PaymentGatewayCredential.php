<?php
namespace App\Domain\Platform\Models;
use Illuminate\Database\Eloquent\Model;
/** Secrets use Laravel's `encrypted` cast (APP_KEY) — never plaintext at rest. */
class PaymentGatewayCredential extends Model
{
    protected $fillable = ['provider','api_key','api_secret','webhook_secret','is_active','is_test_mode'];
    protected function casts(): array { return ['api_key'=>'encrypted','api_secret'=>'encrypted','webhook_secret'=>'encrypted','is_active'=>'boolean','is_test_mode'=>'boolean']; }
    public function isConfigured(): bool { return ! empty($this->api_key) && ! empty($this->api_secret); }
}
