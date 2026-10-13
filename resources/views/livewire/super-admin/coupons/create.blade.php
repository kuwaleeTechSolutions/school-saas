<div class="max-w-xl">
    <h1 class="text-lg font-semibold text-slate-800 mb-6">New Coupon</h1>
    <form wire:submit="save" class="space-y-4">
        <div><label class="block text-sm font-medium text-slate-700">Code *</label><input wire:model="code" type="text" class="mt-1 w-full rounded-lg border-slate-300 text-sm uppercase">@error('code')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror</div>
        <div><label class="block text-sm font-medium text-slate-700">Description</label><input wire:model="description" type="text" class="mt-1 w-full rounded-lg border-slate-300 text-sm"></div>
        <div class="grid grid-cols-2 gap-4">
            <div><label class="block text-sm font-medium text-slate-700">Discount Type *</label><select wire:model="discountType" class="mt-1 w-full rounded-lg border-slate-300 text-sm"><option value="percentage">Percentage</option><option value="fixed_amount">Fixed Amount</option></select></div>
            <div><label class="block text-sm font-medium text-slate-700">Value *</label><input wire:model="value" type="number" step="0.01" class="mt-1 w-full rounded-lg border-slate-300 text-sm">@error('value')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror</div>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div><label class="block text-sm font-medium text-slate-700">Max Total Redemptions</label><input wire:model="maxRedemptions" type="number" placeholder="Unlimited" class="mt-1 w-full rounded-lg border-slate-300 text-sm"></div>
            <div><label class="block text-sm font-medium text-slate-700">Max Per School *</label><input wire:model="maxRedemptionsPerSchool" type="number" min="1" class="mt-1 w-full rounded-lg border-slate-300 text-sm">@error('maxRedemptionsPerSchool')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror</div>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div><label class="block text-sm font-medium text-slate-700">Valid From</label><input wire:model="validFrom" type="date" class="mt-1 w-full rounded-lg border-slate-300 text-sm"></div>
            <div><label class="block text-sm font-medium text-slate-700">Valid Until</label><input wire:model="validUntil" type="date" class="mt-1 w-full rounded-lg border-slate-300 text-sm">@error('validUntil')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror</div>
        </div>
        <div class="flex justify-end gap-3 pt-2 border-t border-slate-100"><a href="{{ route('super-admin.coupons.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm text-slate-700">Cancel</a><button type="submit" class="rounded-lg bg-slate-900 px-4 py-2 text-sm text-white">Create Coupon</button></div>
    </form>
</div>
