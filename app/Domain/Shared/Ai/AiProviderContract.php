<?php
namespace App\Domain\Shared\Ai;
/** Provider abstraction (Section 88): business code never names a vendor. */
interface AiProviderContract
{
    public function isConfigured(): bool;
    /** @param array<int, array{role:string, content:string}> $messages */
    public function complete(array $messages): string;
}
