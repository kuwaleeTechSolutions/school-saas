<?php

namespace App\Livewire\Tenant\Announcements;

use App\Domain\Tenant\Models\Announcement;
use App\Domain\Tenant\Services\AnnouncementService;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;

    public function mount(): void
    {
        $this->authorize('viewAny', Announcement::class);
    }

    public function markRead(int $announcementId, AnnouncementService $announcementService): void
    {
        $announcement = Announcement::findOrFail($announcementId);
        $announcementService->markRead($announcement, auth()->user());
    }

    public function render(AnnouncementService $announcementService)
    {
        $announcements = $announcementService->applicableToQuery(auth()->user())
            ->withCount(['reads as is_read_count' => fn ($q) => $q->where('user_id', auth()->id())])
            ->orderByDesc('published_at')
            ->paginate(15);

        return view('livewire.tenant.announcements.index', ['announcements' => $announcements]);
    }
}
