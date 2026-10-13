<?php
namespace App\Domain\Tenant\Services;
use App\Domain\Shared\Audit\AuditService;
use App\Domain\Tenant\Models\InventoryItem;
use App\Domain\Tenant\Models\InventoryTransaction;
use App\Models\User;
use RuntimeException;

class InventoryService
{
    public function __construct(protected AuditService $audit) {}

    public function receive(InventoryItem $item, int $quantity, ?string $reference, User $actor): InventoryTransaction
    {
        if ($quantity <= 0) throw new RuntimeException('Receipt quantity must be positive.');
        $transaction = InventoryTransaction::create([
            'inventory_item_id' => $item->id, 'type' => 'receipt', 'quantity' => $quantity,
            'reference' => $reference, 'recorded_by' => $actor->id, 'transacted_at' => now(),
        ]);
        $this->audit->logSchool($actor->id, 'inventory.received', InventoryTransaction::class, $transaction->id, null, ['item_id' => $item->id, 'quantity' => $quantity]);
        return $transaction;
    }

    public function issue(InventoryItem $item, int $quantity, ?string $reference, User $actor): InventoryTransaction
    {
        if ($quantity <= 0) throw new RuntimeException('Issue quantity must be positive.');
        if ($item->currentStock() < $quantity) {
            throw new RuntimeException("Insufficient stock: only {$item->currentStock()} {$item->unit} available.");
        }
        $transaction = InventoryTransaction::create([
            'inventory_item_id' => $item->id, 'type' => 'issue', 'quantity' => -$quantity,
            'reference' => $reference, 'recorded_by' => $actor->id, 'transacted_at' => now(),
        ]);
        $this->audit->logSchool($actor->id, 'inventory.issued', InventoryTransaction::class, $transaction->id, null, ['item_id' => $item->id, 'quantity' => $quantity]);
        return $transaction;
    }
}
