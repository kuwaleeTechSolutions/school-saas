<?php
namespace App\Domain\Shared\Ai;
use App\Domain\Shared\Audit\AuditService;
use App\Domain\Shared\Entitlement\Contracts\EntitlementServiceContract;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\RateLimiter;
use RuntimeException;
/**
 * ARCHITECTURE ONLY — no data tools are wired up. This service enforces the rules any future AI feature must keep:
 * plan + role gate, per-user rate limit, no school data in the prompt unless it came through a permission-checked
 * service, and an audit entry that records metadata only (never the question or answer text).
 * Today it can answer only from the question itself; it is NOT a "ask about my school's data" assistant yet.
 */
class AiAssistantService
{
    public function __construct(protected AiProviderContract $provider, protected EntitlementServiceContract $entitlements, protected AuditService $audit) {}
    public function ask(User $user, string $question): string
    {
        if (! $this->entitlements->can('ai_assistant.view')) throw new AuthorizationException('The AI Assistant is not available on your plan or role.');
        if (! $this->provider->isConfigured()) throw new RuntimeException('The AI Assistant is not set up yet.');
        $key = 'ai-ask:'.$user->id;
        if (RateLimiter::tooManyAttempts($key, 20)) throw new RuntimeException('Too many requests. Please try again shortly.');
        RateLimiter::hit($key, 3600);
        $question = mb_substr(trim($question), 0, 2000);
        $answer = $this->provider->complete([
            ['role' => 'system', 'content' => 'You are a school-administration assistant. You have no access to school records; do not claim or invent any.'],
            ['role' => 'user', 'content' => $question],
        ]);
        $this->audit->logSchool($user->id, 'ai.asked', null, null, null, ['question_chars' => mb_strlen($question), 'answer_chars' => mb_strlen($answer)]);
        return $answer;
    }
}
