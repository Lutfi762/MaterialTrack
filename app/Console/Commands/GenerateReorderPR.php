<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Department;
use App\Models\Item;
use App\Models\PurchaseRequest;
use App\Models\User;
use App\Support\DocumentNumber;
use Illuminate\Support\Facades\DB;

class GenerateReorderPR extends Command
{
    protected $signature = "reorder:generate-pr";
    protected $description = "Auto-generate Purchase Request for items below reorder point";

    public function handle()
    {
        $items = Item::where("reorder_point", ">", 0)
            ->where("reorder_qty", ">", 0)
            ->whereRaw("current_stock <= reorder_point")
            ->get();

        if ($items->isEmpty()) {
            $this->info("No items below reorder point.");
            return 0;
        }

        $admin = User::whereHas("roles", function($q) {
            $q->where("name", "admin");
        })->first();

        $userId = $admin ? $admin->id : 1;
        $deptId = $admin?->department_id ?? Department::query()->value('id');

        if (! $deptId) {
            $this->error("No department found; cannot create PR without department_id.");
            return 1;
        }

        $itemsToOrder = [];
        foreach ($items as $item) {
            $hasDraftOrPendingPR = PurchaseRequest::whereIn("status", ["draft", "pending"])
                ->whereHas("items", function($q) use ($item) {
                    $q->where("item_id", $item->id);
                })->exists();

            if (!$hasDraftOrPendingPR) {
                $itemsToOrder[] = $item;
            }
        }

        if (empty($itemsToOrder)) {
            $this->info("Low stock items already have active PRs.");
            return 0;
        }

        DB::transaction(function() use ($itemsToOrder, $userId, $deptId) {
            $pr = PurchaseRequest::create([
                "pr_number" => DocumentNumber::next("purchase_requests", "PR"),
                "request_date" => now()->toDateString(),
                "needed_date" => now()->addDays(7)->toDateString(),
                "department_id" => $deptId,
                "requested_by" => $userId,
                "notes" => "Auto-replenishment for low stock items",
                "priority" => "high",
                "status" => "draft",
            ]);

            foreach ($itemsToOrder as $item) {
                $pr->items()->create([
                    "item_id" => $item->id,
                    "qty" => $item->reorder_qty,
                    "unit" => $item->unit,
                    "notes" => "Auto PR (Stock: " . $item->current_stock . " <= " . $item->reorder_point . ")"
                ]);
            }
        });

        $this->info("Generated auto-PR for " . count($itemsToOrder) . " items.");
        return 0;
    }
}
