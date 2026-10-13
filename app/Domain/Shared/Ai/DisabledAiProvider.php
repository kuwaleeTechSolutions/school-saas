<?php
namespace App\Domain\Shared\Ai;
use RuntimeException;
/** Default binding. Honest: no model is connected, so it refuses rather than inventing an answer. */
class DisabledAiProvider implements AiProviderContract
{
    public function isConfigured(): bool { return false; }
    public function complete(array $messages): string { throw new RuntimeException('No AI provider is configured for this platform.'); }
}
