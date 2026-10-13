<?php
namespace App\Domain\Tenant\Models;
use App\Domain\Shared\Tenancy\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;

class CertificateTemplate extends Model {
    use BelongsToSchool;
    protected $fillable = ['school_id','name','type','body_template','is_active'];
    protected function casts(): array { return ['is_active'=>'boolean']; }
    public function render(array $values): string {
        $body = $this->body_template;
        foreach ($values as $key => $value) { $body = str_replace('{{' . $key . '}}', (string) $value, $body); }
        return $body;
    }
}
