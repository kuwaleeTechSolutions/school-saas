<?php
namespace App\Http\Controllers;
use App\Domain\Platform\Services\GatewayWebhookService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use InvalidArgumentException;
use RuntimeException;
/** Public endpoint; protection is signature verification inside GatewayWebhookService (CSRF is exempted at the route). */
class GatewayWebhookController extends Controller
{
    public function __construct(protected GatewayWebhookService $webhooks) {}
    public function handle(Request $request, string $provider): Response
    {
        $signature = match ($provider) {
            'razorpay' => (string) $request->header('X-Razorpay-Signature', ''),
            'stripe' => (string) $request->header('Stripe-Signature', ''),
            default => '',
        };
        try {
            $this->webhooks->handle($provider, $request->getContent(), $signature);
            return response('', 200);
        } catch (InvalidArgumentException $e) {
            return response('Unknown provider.', 404);
        } catch (RuntimeException $e) {
            report($e);
            return response($e->getMessage(), 400);
        }
    }
}
