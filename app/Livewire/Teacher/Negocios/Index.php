<?php

namespace App\Livewire\Teacher\Negocios;

use App\Models\Central\Group;
use App\Models\Central\IntercompanyInvoice;
use App\Models\Central\IntercompanyJournalEntry;
use App\Models\Central\Tenant as CentralTenant;
use App\Models\Tenant\JournalEntry;
use App\Services\IntercompanyService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Mercado del grupo')]
class Index extends Component
{
    // ── Tab activa ─────────────────────────────────────────────────────────────
    public string $tab = 'pendientes';

    // ── Filtro de grupo ────────────────────────────────────────────────────────
    public int $groupFilter = 0; // 0 = todos los grupos visibles al docente

    // ── Modal: ver detalle ─────────────────────────────────────────────────────
    public bool $showDetailModal = false;

    public ?int $detailInvoiceId = null;

    /** @var array<string, array{reference: string, date: string, balanced: bool, total_debit: float, total_credit: float, lines: array}|null> */
    public array $detailJournalData = [];

    // ── Modal: anular transacción ──────────────────────────────────────────────
    public bool $showAnnulModal = false;

    public ?int $annullingId = null;

    public string $anulacion_motivo = '';

    // ── Determinar layout según rol ────────────────────────────────────────────
    public function getListeners(): array
    {
        return [];
    }

    // ── Abrir modal de detalle ─────────────────────────────────────────────────

    public function openDetail(int $id): void
    {
        $invoice = IntercompanyInvoice::find($id);

        if (! $invoice || ! $this->canAccessGroup($invoice->group_id)) {
            return;
        }

        $this->detailInvoiceId = $id;
        $this->detailJournalData = [];

        if ($invoice->isAceptada() || $invoice->isAnulada()) {
            $links = IntercompanyJournalEntry::where('intercompany_invoice_id', $id)->get();

            // Pre-cargar modelos Central antes de cambiar contexto de tenancy
            $tenantModels = $links->mapWithKeys(
                fn ($link) => [$link->tenant_id => CentralTenant::on('pgsql')->find($link->tenant_id)]
            );

            foreach ($links as $link) {
                $tenantModel = $tenantModels[$link->tenant_id] ?? null;
                if (! $tenantModel) {
                    continue;
                }

                $data = $tenantModel->run(function () use ($link): ?array {
                    $entry = JournalEntry::with('lines.account')->find($link->journal_entry_id);
                    if (! $entry) {
                        return null;
                    }

                    $totalDebit = (float) $entry->lines->sum('debit');
                    $totalCredit = (float) $entry->lines->sum('credit');

                    return [
                        'reference' => $entry->reference ?? '',
                        'date' => $entry->date ? (is_string($entry->date) ? $entry->date : $entry->date->format('d/m/Y')) : '',
                        'total_debit' => $totalDebit,
                        'total_credit' => $totalCredit,
                        'balanced' => abs($totalDebit - $totalCredit) < 0.01,
                        'lines' => $entry->lines->map(fn ($line) => [
                            'code' => $line->account?->code ?? '—',
                            'name' => $line->account?->name ?? '—',
                            'debit' => (float) $line->debit,
                            'credit' => (float) $line->credit,
                        ])->toArray(),
                    ];
                });

                $this->detailJournalData[$link->party] = $data;
            }
        }

        $this->showDetailModal = true;
    }

    public function closeDetail(): void
    {
        $this->showDetailModal = false;
        $this->detailInvoiceId = null;
        $this->detailJournalData = [];
    }

    // ── Abrir modal de anulación ───────────────────────────────────────────────

    public function openAnnul(int $id): void
    {
        $this->annullingId = $id;
        $this->anulacion_motivo = '';
        $this->showAnnulModal = true;
        $this->showDetailModal = false;
    }

    public function confirmAnnul(IntercompanyService $service): void
    {
        $this->validate([
            'anulacion_motivo' => ['required', 'string', 'min:10', 'max:500'],
        ], [
            'anulacion_motivo.required' => 'Escribe el motivo de anulación.',
            'anulacion_motivo.min' => 'El motivo debe tener al menos 10 caracteres.',
        ]);

        $invoice = IntercompanyInvoice::findOrFail($this->annullingId);

        // Verificar que el docente/coordinador tiene acceso a este grupo
        if (! $this->canAccessGroup($invoice->group_id)) {
            $this->dispatch('notify', type: 'error', message: 'No tienes acceso a esta transacción.');

            return;
        }

        try {
            $service->annul($invoice, $this->anulacion_motivo, auth()->id());

            $this->showAnnulModal = false;
            $this->annullingId = null;
            $this->dispatch('notify', type: 'success',
                message: "Transacción {$invoice->consecutive} anulada. Los asientos contables han sido revertidos.");

        } catch (\Throwable $e) {
            $this->dispatch('notify', type: 'error',
                message: 'Error al anular: '.$e->getMessage());
        }
    }

    // ── Render ─────────────────────────────────────────────────────────────────

    public function render(): mixed
    {
        $user = auth()->user();
        $groups = $this->getVisibleGroups($user);
        $groupIds = $groups->pluck('id');

        // Filtrar por grupo específico si se seleccionó uno
        $activeGroupIds = $this->groupFilter
            ? collect([$this->groupFilter])
            : $groupIds;

        $pendientes = IntercompanyInvoice::with(['seller', 'buyer', 'items'])
            ->whereIn('group_id', $activeGroupIds)
            ->where('status', 'pendiente')
            ->orderByDesc('created_at')
            ->get();

        $historial = IntercompanyInvoice::with(['seller', 'buyer', 'anuladoPor'])
            ->whereIn('group_id', $activeGroupIds)
            ->whereIn('status', ['aceptada', 'rechazada', 'anulada'])
            ->orderByDesc('updated_at')
            ->get();

        // ── Ranking por estudiante ─────────────────────────────────────────────
        $allClosed = IntercompanyInvoice::whereIn('group_id', $activeGroupIds)
            ->where('status', 'aceptada')
            ->get();

        $ranking = CentralTenant::whereIn('group_id', $activeGroupIds)
            ->where('type', 'student')
            ->where('active', true)
            ->orderBy('company_name')
            ->get()
            ->map(function (CentralTenant $t) use ($allClosed) {
                $ventas = $allClosed->where('seller_tenant_id', $t->id);
                $compras = $allClosed->where('buyer_tenant_id', $t->id);

                return [
                    'tenant' => $t,
                    'ventas' => $ventas->count(),
                    'total_ventas' => $ventas->sum('total'),
                    'compras' => $compras->count(),
                    'total_compras' => $compras->sum('total'),
                    'con_retencion' => $ventas->where('retencion_fuente', '>', 0)->count()
                                      + $compras->where('retencion_fuente', '>', 0)->count(),
                    'ciclo_completo' => $ventas->count() > 0 && $compras->count() > 0,
                ];
            })
            ->sortByDesc('ventas');

        // ── Estadísticas globales ──────────────────────────────────────────────
        $stats = [
            'pendientes' => IntercompanyInvoice::whereIn('group_id', $activeGroupIds)->where('status', 'pendiente')->count(),
            'aceptadas' => IntercompanyInvoice::whereIn('group_id', $activeGroupIds)->where('status', 'aceptada')->count(),
            'rechazadas' => IntercompanyInvoice::whereIn('group_id', $activeGroupIds)->where('status', 'rechazada')->count(),
            'anuladas' => IntercompanyInvoice::whereIn('group_id', $activeGroupIds)->where('status', 'anulada')->count(),
            'volumen' => IntercompanyInvoice::whereIn('group_id', $activeGroupIds)->where('status', 'aceptada')->sum('total'),
        ];

        $layout = $this->resolveLayout($user);

        $detailInvoice = $this->detailInvoiceId
            ? IntercompanyInvoice::with(['seller', 'buyer', 'items'])->find($this->detailInvoiceId)
            : null;

        return view('livewire.teacher.negocios.index', compact(
            'groups', 'pendientes', 'historial', 'ranking', 'stats', 'detailInvoice'
        ))->layout($layout);
    }

    // ── Helpers ────────────────────────────────────────────────────────────────

    private function getVisibleGroups($user)
    {
        if ($user->role->value === 'coordinator') {
            $institution = $user->institution;

            return $institution
                ? Group::where('institution_id', $institution->id)->with('teacher')->get()
                : collect();
        }

        // teacher
        return Group::where('teacher_id', $user->id)->get();
    }

    private function canAccessGroup(int $groupId): bool
    {
        $user = auth()->user();
        $group = Group::find($groupId);

        if (! $group) {
            return false;
        }

        if ($user->role->value === 'coordinator') {
            return $user->institution?->id === $group->institution_id;
        }

        return $group->teacher_id === $user->id;
    }

    private function resolveLayout($user): string
    {
        return match ($user->role->value) {
            'coordinator' => 'layouts.coordinator',
            default => 'layouts.teacher',
        };
    }
}
