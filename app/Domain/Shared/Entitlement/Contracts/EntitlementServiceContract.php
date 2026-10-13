<?php
namespace App\Domain\Shared\Entitlement\Contracts;
interface EntitlementServiceContract
{
    public function moduleEnabled(string $moduleCode): bool;
    public function featureEnabled(string $featureCode): bool;
    public function permissionAllowedByPlan(string $permissionCode): bool;
    public function limitAvailable(string $metric, int $requested = 1): bool;
    public function remaining(string $metric): ?int;
    public function can(string $permissionCode): bool;
}
