<div class="max-w-3xl mx-auto">
    <div class="mb-8">
        <div class="flex items-center justify-between text-sm text-slate-500 mb-2"><span>Setup progress</span><span>{{ $currentStep }} / 10</span></div>
        <div class="h-2 w-full rounded-full bg-slate-200"><div class="h-2 rounded-full bg-teal-600" style="width: {{ $currentStep / 10 * 100 }}%"></div></div>
    </div>

    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        @if ($currentStep === 1)
            <h2 class="text-lg font-semibold text-slate-800 mb-1">School Information</h2>
            <form wire:submit="saveSchoolInfo" class="space-y-4">
                <div><label class="block text-sm font-medium text-slate-700">School Name</label><input wire:model="schoolName" type="text" class="mt-1 w-full rounded-lg border-slate-300 text-sm">@error('schoolName') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror</div>
                <div class="grid grid-cols-2 gap-4">
                    <div><label class="block text-sm font-medium text-slate-700">Email</label><input wire:model="schoolEmail" type="email" class="mt-1 w-full rounded-lg border-slate-300 text-sm"></div>
                    <div><label class="block text-sm font-medium text-slate-700">Phone</label><input wire:model="schoolPhone" type="text" class="mt-1 w-full rounded-lg border-slate-300 text-sm"></div>
                </div>
                <div><label class="block text-sm font-medium text-slate-700">Address</label><textarea wire:model="schoolAddress" rows="2" class="mt-1 w-full rounded-lg border-slate-300 text-sm"></textarea></div>
                <div class="grid grid-cols-2 gap-4">
                    <div><label class="block text-sm font-medium text-slate-700">Board</label><input wire:model="board" type="text" class="mt-1 w-full rounded-lg border-slate-300 text-sm"></div>
                    <div><label class="block text-sm font-medium text-slate-700">Affiliation Number</label><input wire:model="affiliationNumber" type="text" class="mt-1 w-full rounded-lg border-slate-300 text-sm"></div>
                </div>
                <div class="flex justify-end pt-2"><button type="submit" class="rounded-lg bg-teal-700 px-4 py-2 text-sm text-white">Save & Continue</button></div>
            </form>
        @elseif ($currentStep === 2)
            <h2 class="text-lg font-semibold text-slate-800 mb-1">Branding</h2>
            <form wire:submit="saveBranding" class="space-y-4">
                <input wire:model="logo" type="file" accept="image/*" class="block text-sm">@error('logo') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                <div class="flex justify-end gap-3 pt-2"><button type="button" wire:click="skipStep" class="rounded-lg border border-slate-300 px-4 py-2 text-sm text-slate-600">Skip for now</button><button type="submit" class="rounded-lg bg-teal-700 px-4 py-2 text-sm text-white">Save & Continue</button></div>
            </form>
        @elseif ($currentStep === 3)
            <h2 class="text-lg font-semibold text-slate-800 mb-1">Academic Session</h2>
            <form wire:submit="saveAcademicSession" class="space-y-4">
                <div><label class="block text-sm font-medium text-slate-700">Session Name</label><input wire:model="sessionName" type="text" placeholder="2026-27" class="mt-1 w-full rounded-lg border-slate-300 text-sm">@error('sessionName') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror</div>
                <div class="grid grid-cols-2 gap-4">
                    <div><label class="block text-sm font-medium text-slate-700">Starts On</label><input wire:model="sessionStartsOn" type="date" class="mt-1 w-full rounded-lg border-slate-300 text-sm">@error('sessionStartsOn') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror</div>
                    <div><label class="block text-sm font-medium text-slate-700">Ends On</label><input wire:model="sessionEndsOn" type="date" class="mt-1 w-full rounded-lg border-slate-300 text-sm">@error('sessionEndsOn') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror</div>
                </div>
                <div class="flex justify-end pt-2"><button type="submit" class="rounded-lg bg-teal-700 px-4 py-2 text-sm text-white">Save & Continue</button></div>
            </form>
        @elseif ($currentStep === 4)
            <h2 class="text-lg font-semibold text-slate-800 mb-1">Classes</h2>
            <form wire:submit="saveClasses" class="space-y-4">
                <textarea wire:model="classNamesBulk" rows="6" class="w-full rounded-lg border-slate-300 text-sm font-mono"></textarea>
                @error('classNamesBulk') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                @error('limit') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                <div class="flex justify-end pt-2"><button type="submit" class="rounded-lg bg-teal-700 px-4 py-2 text-sm text-white">Save & Continue</button></div>
            </form>
        @elseif ($currentStep === 5)
            <h2 class="text-lg font-semibold text-slate-800 mb-1">Sections</h2>
            <form wire:submit="saveSections" class="space-y-4">
                <textarea wire:model="sectionNamesBulk" rows="4" class="w-full rounded-lg border-slate-300 text-sm font-mono"></textarea>
                @error('sectionNamesBulk') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                <div class="flex justify-end pt-2"><button type="submit" class="rounded-lg bg-teal-700 px-4 py-2 text-sm text-white">Save & Continue</button></div>
            </form>
        @elseif ($currentStep === 6)
            <h2 class="text-lg font-semibold text-slate-800 mb-1">Subjects</h2>
            <form wire:submit="saveSubjects" class="space-y-4">
                <textarea wire:model="subjectNamesBulk" rows="6" class="w-full rounded-lg border-slate-300 text-sm font-mono"></textarea>
                @error('subjectNamesBulk') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                <div class="flex justify-end pt-2"><button type="submit" class="rounded-lg bg-teal-700 px-4 py-2 text-sm text-white">Save & Continue</button></div>
            </form>
        @elseif ($currentStep === 7)
            <h2 class="text-lg font-semibold text-slate-800 mb-1">Admin Users</h2>
            <form wire:submit="addAdminUser" class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div><label class="block text-sm font-medium text-slate-700">Name</label><input wire:model="newAdminName" type="text" class="mt-1 w-full rounded-lg border-slate-300 text-sm">@error('newAdminName') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror</div>
                    <div><label class="block text-sm font-medium text-slate-700">Email</label><input wire:model="newAdminEmail" type="email" class="mt-1 w-full rounded-lg border-slate-300 text-sm">@error('newAdminEmail') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror</div>
                </div>
                @error('limit') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                <div class="flex justify-between pt-2"><button type="submit" class="rounded-lg border border-slate-300 px-4 py-2 text-sm text-slate-700">+ Add Admin</button><button type="button" wire:click="finishAdminUsersStep" class="rounded-lg bg-teal-700 px-4 py-2 text-sm text-white">Continue</button></div>
            </form>
        @elseif ($currentStep === 8)
            @php $subscription = app(\App\Domain\Shared\Tenancy\TenantContext::class)->school()->activeSubscription()->with('plan.modules')->first(); @endphp
            <h2 class="text-lg font-semibold text-slate-800 mb-1">Package Confirmation</h2>
            <div class="rounded-lg border border-slate-200 p-4 mb-5">
                <div class="font-medium text-slate-800">{{ $subscription?->plan?->name ?? 'No active plan' }}</div>
                <div class="text-sm text-slate-500 mt-1">Status: {{ $subscription ? str($subscription->status)->headline() : '—' }}</div>
                <div class="mt-3 flex flex-wrap gap-2">@foreach ($subscription?->plan?->modules ?? [] as $module)<span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs text-slate-700">{{ $module->name }}</span>@endforeach</div>
            </div>
            <div class="flex justify-end"><button wire:click="confirmPackage" class="rounded-lg bg-teal-700 px-4 py-2 text-sm text-white">Confirm & Continue</button></div>
        @elseif ($currentStep === 9)
            <h2 class="text-lg font-semibold text-slate-800 mb-1">Import Students</h2>
            <div class="space-y-4">
                <input wire:model="studentsCsv" type="file" accept=".csv" class="block text-sm">@error('studentsCsv') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                <button wire:click="previewImport" class="rounded-lg border border-slate-300 px-4 py-2 text-sm text-slate-700">Validate & Preview</button>
                @if (!empty($importErrors))<div class="rounded-lg bg-red-50 border border-red-200 p-3 text-sm text-red-700">@foreach ($importErrors as $err)<div>{{ $err }}</div>@endforeach</div>@endif
                @if (!empty($importPreview))<p class="text-sm text-slate-500">{{ count($importPreview) }} students will be created.</p>@endif
                @error('limit') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                <div class="flex justify-between pt-2">
                    <button type="button" wire:click="skipStep" class="rounded-lg border border-slate-300 px-4 py-2 text-sm text-slate-600">Skip for now</button>
                    @if (!empty($importPreview))<button wire:click="confirmImport" class="rounded-lg bg-teal-700 px-4 py-2 text-sm text-white">Confirm Import</button>@endif
                </div>
            </div>
        @elseif ($currentStep === 10)
            <div class="text-center py-8">
                <h2 class="text-lg font-semibold text-slate-800">You're all set!</h2>
                <button wire:click="completeSetup" class="mt-6 rounded-lg bg-teal-700 px-5 py-2.5 text-sm text-white">Go to Dashboard</button>
            </div>
        @endif
    </div>
</div>
