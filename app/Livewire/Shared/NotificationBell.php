<?php

namespace App\Livewire\Shared;

use App\Models\Central\PlatformNotification;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class NotificationBell extends Component
{
    public bool $open = false;

    #[Computed]
    public function unreadCount(): int
    {
        return PlatformNotification::forUser(auth()->id())
            ->unread()
            ->count();
    }

    #[Computed]
    public function notifications(): Collection
    {
        return PlatformNotification::forUser(auth()->id())
            ->latest()
            ->limit(10)
            ->get();
    }

    public function markAllRead(): void
    {
        PlatformNotification::forUser(auth()->id())
            ->unread()
            ->update(['read_at' => now()]);

        unset($this->unreadCount, $this->notifications);
    }

    public function markRead(int $id): void
    {
        $notification = PlatformNotification::forUser(auth()->id())->findOrFail($id);
        $notification->markAsRead();

        unset($this->unreadCount, $this->notifications);
    }

    #[On('notification-sent')]
    public function refresh(): void
    {
        unset($this->unreadCount, $this->notifications);
    }

    public function render(): View
    {
        return view('livewire.shared.notification-bell');
    }
}
