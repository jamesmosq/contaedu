<?php

namespace App\Livewire\Admin;

use App\Enums\CommunicationAudience;
use App\Enums\NotificationType;
use App\Enums\UserRole;
use App\Models\Central\Communication;
use App\Models\Central\PlatformNotification;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
#[Title('Comunicaciones')]
class Comunicaciones extends Component
{
    public bool $showForm = false;

    #[Rule(['required', 'string', 'max:150'])]
    public string $title = '';

    #[Rule(['required', 'string', 'max:2000'])]
    public string $body = '';

    #[Rule(['required', 'in:announcement,maintenance,update,urgent'])]
    public string $type = 'announcement';

    #[Rule(['required', 'in:all,coordinators,teachers'])]
    public string $audience = 'all';

    #[Computed]
    public function communications(): Collection
    {
        return Communication::query()
            ->with('sender')
            ->latest()
            ->get();
    }

    #[Computed]
    public function audienceOptions(): array
    {
        return CommunicationAudience::cases();
    }

    #[Computed]
    public function typeOptions(): array
    {
        return [
            NotificationType::Announcement,
            NotificationType::Maintenance,
            NotificationType::Update,
            NotificationType::Urgent,
        ];
    }

    public function openForm(): void
    {
        $this->reset('title', 'body', 'type', 'audience');
        $this->type = 'announcement';
        $this->audience = 'all';
        $this->showForm = true;
    }

    public function send(): void
    {
        $this->validate();

        $audienceEnum = CommunicationAudience::from($this->audience);
        $typeEnum = NotificationType::from($this->type);
        $sender = auth('web')->user();

        $recipients = $this->resolveRecipients($audienceEnum);

        $communication = Communication::create([
            'from_user_id' => $sender->id,
            'title' => $this->title,
            'body' => $this->body,
            'type' => $typeEnum,
            'audience' => $audienceEnum,
            'recipient_count' => $recipients->count(),
            'sent_at' => now(),
        ]);

        $now = now();
        $notifications = $recipients->map(fn (User $user) => [
            'from_user_id' => $sender->id,
            'to_user_id' => $user->id,
            'type' => $typeEnum->value,
            'subject' => $this->title,
            'body' => $this->body,
            'read_at' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ])->values()->all();

        PlatformNotification::insert($notifications);

        $this->showForm = false;
        $this->reset('title', 'body');
        unset($this->communications);

        $this->dispatch('notify', type: 'success', message: "Comunicación enviada a {$communication->recipient_count} usuario(s).");
    }

    private function resolveRecipients(CommunicationAudience $audience): Collection
    {
        return match ($audience) {
            CommunicationAudience::All => User::query()
                ->whereIn('role', [UserRole::Coordinator->value, UserRole::Teacher->value])
                ->get(),
            CommunicationAudience::Coordinators => User::query()
                ->where('role', UserRole::Coordinator->value)
                ->get(),
            CommunicationAudience::Teachers => User::query()
                ->where('role', UserRole::Teacher->value)
                ->get(),
        };
    }

    public function render(): View
    {
        return view('livewire.admin.comunicaciones');
    }
}
