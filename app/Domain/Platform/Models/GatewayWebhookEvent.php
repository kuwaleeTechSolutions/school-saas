<?php
namespace App\Domain\Platform\Models;
use Illuminate\Database\Eloquent\Model;
class GatewayWebhookEvent extends Model
{
    protected $fillable = ['provider','external_event_id','event_type','payload','status','failure_reason','processed_at'];
    protected function casts(): array { return ['payload'=>'array','processed_at'=>'datetime']; }
}
