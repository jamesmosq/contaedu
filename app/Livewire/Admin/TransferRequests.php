<?php

namespace App\Livewire\Admin;

use App\Models\Central\TransferRequest;
use App\Services\TransferRequestService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
class TransferRequests extends Component
{
    use WithPagination;

    public string $filter = 'pending';

    public string $adminNotes = '';

    public ?int $actionId = null;

    public string $actionType = ''; // 'approve' | 'reject'

    #[Computed]
    public function requests(): LengthAwarePaginator
    {
        return TransferRequest::with(['requester', 'tenant.group.institution', 'targetGroup.institution'])
            ->when($this->filter === 'pending', fn ($q) => $q->pending())
            ->when($this->filter !== 'pending', fn ($q) => $q->where('status', $this->filter))
            ->latest()
            ->paginate(15);
    }

    public function openAction(int $id, string $type): void
    {
        $this->actionId = $id;
        $this->actionType = $type;
        $this->adminNotes = '';
    }

    public function cancelAction(): void
    {
        $this->actionId = null;
        $this->actionType = '';
        $this->adminNotes = '';
    }

    public function executeAction(): void
    {
        $this->validate(['adminNotes' => 'nullable|string|max:500']);

        $request = TransferRequest::findOrFail($this->actionId);
        $service = app(TransferRequestService::class);

        try {
            if ($this->actionType === 'approve') {
                $service->approve(auth()->user(), $request, $this->adminNotes ?: null);
                session()->flash('success', 'Transferencia aprobada y ejecutada.');
            } else {
                $this->validate(['adminNotes' => 'required|string|max:500']);
                $service->reject(auth()->user(), $request, $this->adminNotes);
                session()->flash('success', 'Solicitud rechazada.');
            }
        } catch (\DomainException $e) {
            session()->flash('error', $e->getMessage());
        }

        $this->cancelAction();
        unset($this->requests);
    }

    public function updatedFilter(): void
    {
        $this->resetPage();
        unset($this->requests);
    }

    public function render(): View
    {
        return view('livewire.admin.transfer-requests')
            ->title('Transferencias — Superadmin');
    }
}
