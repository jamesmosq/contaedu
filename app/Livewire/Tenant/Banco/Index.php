<?php

namespace App\Livewire\Tenant\Banco;

use App\Models\Tenant\BankAccount;
use App\Models\Tenant\BankCheck;
use App\Models\Tenant\BankDocument;
use App\Models\Tenant\BankTransaction;
use App\Models\Tenant\CompanyConfig;
use App\Services\BankService;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.tenant')]
#[Title('Banco')]
class Index extends Component
{
    // ── Pestaña activa ───────────────────────────────────────────────────────
    public string $tab = 'movimientos'; // movimientos | transferencias | segunda_cuenta | documentos

    // ── Cuenta activa seleccionada ───────────────────────────────────────────
    public ?int $cuentaActivaId = null;

    // ── Formulario segunda cuenta ────────────────────────────────────────────
    public string $nuevoBanco       = '';
    public string $nuevaTipoCuenta  = 'corriente';
    public float  $montoInicial     = 0;
    public bool   $showSegundaCuenta = false;

    // ── Transferencia entre cuentas propias ─────────────────────────────────
    public bool  $showTransferenciaForm = false;
    public ?int  $transf_origen_id      = null;
    public ?int  $transf_destino_id     = null;
    public float $transf_monto          = 0;

    // ── Emitir cheque ─────────────────────────────────────────────────────────
    public bool   $showChequeForm  = false;
    public string $cheque_beneficiario = '';
    public float  $cheque_valor    = 0;

    // ── Fin de mes ───────────────────────────────────────────────────────────
    public bool $showFinMesConfirm = false;

    // URL de la página de banco (capturada en mount para evitar la URL del endpoint Livewire en updates)
    public string $bancoPageUrl = '';

    public function mount(): void
    {
        $principal = BankAccount::where('es_principal', true)->where('activa', true)->first();
        $this->cuentaActivaId = $principal?->id;
        $this->bancoPageUrl   = url()->current();
    }

    // ── Getters computados ───────────────────────────────────────────────────

    public function getCuentasProperty()
    {
        return BankAccount::where('activa', true)->orderByDesc('es_principal')->get();
    }

    public function getCuentaActivaProperty(): ?BankAccount
    {
        if (! $this->cuentaActivaId) {
            return null;
        }

        return BankAccount::find($this->cuentaActivaId);
    }

    public function getMovimientosProperty()
    {
        if (! $this->cuentaActivaId) {
            return collect();
        }

        return BankTransaction::where('bank_account_id', $this->cuentaActivaId)
            ->orderByDesc('fecha_transaccion')
            ->orderByDesc('id')
            ->limit(100)
            ->get();
    }

    public function getDocumentosProperty()
    {
        $ids = BankAccount::where('activa', true)->pluck('id');

        if ($ids->isEmpty()) {
            return collect();
        }

        return BankDocument::with('bankAccount')
            ->whereIn('bank_account_id', $ids)
            ->orderByDesc('generado_at')
            ->get();
    }

    public function getChequesProperty()
    {
        if (! $this->cuentaActivaId) {
            return collect();
        }

        return BankCheck::where('bank_account_id', $this->cuentaActivaId)
            ->orderByDesc('fecha_emision')
            ->get();
    }

    public function getSaldoTotalProperty(): float
    {
        return BankAccount::where('activa', true)->sum('saldo');
    }

    // ── Seleccionar cuenta ───────────────────────────────────────────────────

    public function seleccionarCuenta(int $id): void
    {
        $this->cuentaActivaId = $id;
    }

    // ── Segunda cuenta ───────────────────────────────────────────────────────

    public function abrirFormSegundaCuenta(): void
    {
        $this->showSegundaCuenta = true;
        $this->tab = 'segunda_cuenta';

        $cuentas = BankAccount::where('activa', true)->get();

        if ($cuentas->isEmpty()) {
            // Sin cuentas: mostrar todos los bancos disponibles
            $this->nuevoBanco = 'bancolombia';
        } else {
            // Banco disponible = el que no tiene la cuenta principal
            $bancosActuales = $cuentas->pluck('bank')->toArray();
            $bancos = ['bancolombia', 'davivienda', 'banco_bogota'];
            $disponibles = array_filter($bancos, fn ($b) => ! in_array($b, $bancosActuales));
            $this->nuevoBanco = array_values($disponibles)[0] ?? '';
        }
    }

    public function abrirSegundaCuenta(): void
    {
        $cuentas = BankAccount::where('activa', true)->get();

        if ($cuentas->count() >= 2) {
            $this->dispatch('notify', type: 'error', message: 'Ya tienes el máximo de 2 cuentas activas.');

            return;
        }

        if (! $cuentas->isEmpty()) {
            $bancoActual = $cuentas->pluck('bank')->toArray();
            if (in_array($this->nuevoBanco, $bancoActual)) {
                $this->dispatch('notify', type: 'error', message: 'La segunda cuenta debe ser en un banco diferente al de tu cuenta actual.');

                return;
            }
        }

        if ($this->montoInicial <= 0) {
            $this->dispatch('notify', type: 'error', message: 'Define el monto inicial a consignar.');

            return;
        }

        // ── Caso 1: No hay ninguna cuenta — crear la primera cuenta (principal) ──
        if ($cuentas->isEmpty()) {
            $nuevaCuenta = BankAccount::create([
                'bank'                 => $this->nuevoBanco,
                'account_number'       => BankService::generarNumeroCuenta($this->nuevoBanco),
                'account_type'         => $this->nuevaTipoCuenta,
                'saldo'                => $this->montoInicial,
                'sobregiro_disponible' => $this->nuevoBanco === 'banco_bogota' && $this->nuevaTipoCuenta === 'corriente' ? 5_000_000 : 0,
                'sobregiro_usado'      => 0,
                'es_principal'         => true,
                'activa'               => true,
                'bloqueada'            => false,
                'cheques_disponibles'  => $this->nuevaTipoCuenta === 'corriente' ? 30 : null,
                'cheques_emitidos'     => 0,
                'fecha_apertura'       => now()->toDateString(),
            ]);

            BankTransaction::create([
                'bank_account_id'   => $nuevaCuenta->id,
                'tipo'              => 'consignacion',
                'valor'             => $this->montoInicial,
                'gmf'               => 0,
                'comision'          => 0,
                'saldo_despues'     => $this->montoInicial,
                'descripcion'       => 'Consignación inicial — apertura de cuenta bancaria',
                'fecha_transaccion' => now()->toDateString(),
            ]);

            $this->showSegundaCuenta = false;
            $this->cuentaActivaId    = $nuevaCuenta->id;
            $this->tab               = 'movimientos';

            $this->dispatch('notify', type: 'success', message: 'Cuenta bancaria en '.$nuevaCuenta->nombreBanco().' creada exitosamente.');

            return;
        }

        // ── Caso 2: Ya hay una cuenta — abrir segunda con transferencia desde la principal ──
        $cuentaOrigen = BankAccount::where('activa', true)->where('es_principal', true)->first();
        if (! $cuentaOrigen) {
            $this->dispatch('notify', type: 'error', message: 'No se encontró tu cuenta principal.');

            return;
        }

        // Calcular costos: si es banco diferente → ACH + GMF
        $comisionAch = BankService::costoAch($cuentaOrigen->bank, $this->nuevoBanco);
        $gmf         = BankService::calcularGmf('transferencia_salida', $this->montoInicial);
        $totalCargo  = $this->montoInicial + $comisionAch + $gmf;

        if ($cuentaOrigen->saldo < $totalCargo) {
            $this->dispatch('notify', type: 'error', message: 'Saldo insuficiente para cubrir el monto + comisiones (ACH: $'.number_format($comisionAch).' + GMF: $'.number_format($gmf).').');

            return;
        }

        // Crear la nueva cuenta
        $nuevaCuenta = BankAccount::create([
            'bank'                 => $this->nuevoBanco,
            'account_number'       => BankService::generarNumeroCuenta($this->nuevoBanco),
            'account_type'         => $this->nuevaTipoCuenta,
            'saldo'                => $this->montoInicial,
            'sobregiro_disponible' => $this->nuevoBanco === 'banco_bogota' && $this->nuevaTipoCuenta === 'corriente' ? 5_000_000 : 0,
            'sobregiro_usado'      => 0,
            'es_principal'         => false,
            'activa'               => true,
            'bloqueada'            => false,
            'cheques_disponibles'  => $this->nuevaTipoCuenta === 'corriente' ? 30 : null,
            'cheques_emitidos'     => 0,
            'fecha_apertura'       => now()->toDateString(),
        ]);

        // Registrar salida en cuenta origen
        $cuentaOrigen->decrement('saldo', $totalCargo);
        BankTransaction::create([
            'bank_account_id'   => $cuentaOrigen->id,
            'tipo'              => 'transferencia_salida',
            'valor'             => $this->montoInicial,
            'gmf'               => $gmf,
            'comision'          => $comisionAch,
            'saldo_despues'     => $cuentaOrigen->fresh()->saldo,
            'descripcion'       => 'Apertura cuenta '.$nuevaCuenta->nombreBanco().' '.$this->nuevaTipoCuenta,
            'banco_destino'     => $this->nuevoBanco,
            'cuenta_destino'    => $nuevaCuenta->account_number,
            'fecha_transaccion' => now()->toDateString(),
        ]);

        // Registrar entrada en cuenta nueva
        BankTransaction::create([
            'bank_account_id'   => $nuevaCuenta->id,
            'tipo'              => 'consignacion',
            'valor'             => $this->montoInicial,
            'gmf'               => 0,
            'comision'          => 0,
            'saldo_despues'     => $this->montoInicial,
            'descripcion'       => 'Consignación inicial apertura de cuenta',
            'fecha_transaccion' => now()->toDateString(),
        ]);

        $this->showSegundaCuenta = false;
        $this->cuentaActivaId    = $nuevaCuenta->id;
        $this->tab               = 'movimientos';

        $this->dispatch('notify', type: 'success', message: 'Cuenta en '.$nuevaCuenta->nombreBanco().' abierta exitosamente.');
    }

    // ── Transferencia entre cuentas propias ─────────────────────────────────

    public function transferirEntreCuentas(): void
    {
        if (! $this->transf_origen_id || ! $this->transf_destino_id) {
            $this->dispatch('notify', type: 'error', message: 'Selecciona cuenta de origen y destino.');
            return;
        }
        if ($this->transf_origen_id === $this->transf_destino_id) {
            $this->dispatch('notify', type: 'error', message: 'Origen y destino no pueden ser la misma cuenta.');
            return;
        }
        if ($this->transf_monto <= 0) {
            $this->dispatch('notify', type: 'error', message: 'El monto debe ser mayor a $0.');
            return;
        }

        $origen  = BankAccount::find($this->transf_origen_id);
        $destino = BankAccount::find($this->transf_destino_id);

        if (! $origen || ! $destino) {
            return;
        }

        $ach     = BankService::costoAch($origen->bank, $destino->bank);
        $gmf     = BankService::calcularGmf('transferencia_salida', $this->transf_monto);
        $cargo   = $this->transf_monto + $gmf + $ach;

        if ($origen->saldoDisponible() < $cargo) {
            $this->dispatch('notify', type: 'error',
                message: 'Saldo insuficiente. Necesitas $' . number_format($cargo, 0, ',', '.') . ' (incluye GMF $' . number_format($gmf, 0, ',', '.') . ($ach > 0 ? ' + ACH $' . number_format($ach, 0, ',', '.') : '') . ')');
            return;
        }

        DB::transaction(function () use ($origen, $destino, $ach, $gmf, $cargo) {
            $origen->decrement('saldo', $cargo);
            BankTransaction::create([
                'bank_account_id'   => $origen->id,
                'tipo'              => 'transferencia_salida',
                'valor'             => $this->transf_monto,
                'gmf'               => $gmf,
                'comision'          => $ach,
                'saldo_despues'     => $origen->fresh()->saldo,
                'descripcion'       => 'Transferencia a ' . $destino->nombreBanco() . '***' . $destino->ultimosDigitos(),
                'banco_destino'     => $destino->bank,
                'cuenta_destino'    => $destino->account_number,
                'fecha_transaccion' => now()->toDateString(),
            ]);

            $destino->increment('saldo', $this->transf_monto);
            BankTransaction::create([
                'bank_account_id'   => $destino->id,
                'tipo'              => 'transferencia_entrada',
                'valor'             => $this->transf_monto,
                'gmf'               => 0,
                'comision'          => 0,
                'saldo_despues'     => $destino->fresh()->saldo,
                'descripcion'       => 'Transferencia desde ' . $origen->nombreBanco() . '***' . $origen->ultimosDigitos(),
                'fecha_transaccion' => now()->toDateString(),
            ]);
        });

        $this->showTransferenciaForm = false;
        $this->reset(['transf_origen_id', 'transf_destino_id', 'transf_monto']);
        $this->dispatch('notify', type: 'success', message: 'Transferencia realizada. GMF: $' . number_format($gmf, 0, ',', '.') . ($ach > 0 ? ' | ACH: $' . number_format($ach, 0, ',', '.') : ''));
    }

    // ── Emitir cheque ─────────────────────────────────────────────────────────

    public function emitirCheque(): void
    {
        $cuenta = $this->cuentaActiva;

        if (! $cuenta || $cuenta->account_type !== 'corriente') {
            $this->dispatch('notify', type: 'error', message: 'Los cheques solo están disponibles en cuentas corrientes.');
            return;
        }
        if (! $this->cheque_beneficiario || $this->cheque_valor <= 0) {
            $this->dispatch('notify', type: 'error', message: 'Completa el beneficiario y el valor del cheque.');
            return;
        }

        $gmf   = BankService::calcularGmf('cheque', $this->cheque_valor);
        $cargo = $this->cheque_valor + $gmf;

        if ($cuenta->saldoDisponible() < $cargo) {
            $this->dispatch('notify', type: 'error',
                message: 'Saldo insuficiente para emitir el cheque (incluye GMF $' . number_format($gmf, 0, ',', '.') . ').');
            return;
        }

        $numeroCheque = str_pad(($cuenta->cheques_emitidos + 1), 4, '0', STR_PAD_LEFT);

        DB::transaction(function () use ($cuenta, $gmf, $cargo, $numeroCheque) {
            $cuenta->decrement('saldo', $cargo);
            $cuenta->increment('cheques_emitidos');

            BankCheck::create([
                'bank_account_id' => $cuenta->id,
                'numero_cheque'   => $numeroCheque,
                'beneficiario'    => $this->cheque_beneficiario,
                'valor'           => $this->cheque_valor,
                'fecha_emision'   => now()->toDateString(),
                'estado'          => 'emitido',
            ]);

            BankTransaction::create([
                'bank_account_id'   => $cuenta->id,
                'tipo'              => 'cheque',
                'valor'             => $this->cheque_valor,
                'gmf'               => $gmf,
                'comision'          => 0,
                'saldo_despues'     => $cuenta->fresh()->saldo,
                'descripcion'       => 'Cheque #' . $numeroCheque . ' — ' . $this->cheque_beneficiario,
                'referencia'        => $numeroCheque,
                'fecha_transaccion' => now()->toDateString(),
            ]);
        });

        $this->showChequeForm = false;
        $this->reset(['cheque_beneficiario', 'cheque_valor']);
        $this->dispatch('notify', type: 'success', message: 'Cheque #' . $numeroCheque . ' emitido por $' . number_format($this->cheque_valor, 0, ',', '.'));
    }

    // ── Documentos bancarios ─────────────────────────────────────────────────

    public function solicitarDocumento(string $tipo, ?int $cuentaId = null): void
    {
        $cuenta = $cuentaId
            ? BankAccount::where('activa', true)->find($cuentaId)
            : $this->cuentaActiva;

        if (! $cuenta) {
            return;
        }

        if ($tipo === 'paz_y_salvo' && $cuenta->saldo != 0) {
            $this->dispatch('notify', type: 'error', message: 'El paz y salvo solo se puede solicitar cuando el saldo de ' . $cuenta->nombreBanco() . ' sea $0.');
            return;
        }

        BankDocument::create([
            'bank_account_id' => $cuenta->id,
            'tipo'            => $tipo,
            'pdf_path'        => '',
            'generado_at'     => now(),
        ]);

        $this->dispatch('notify', type: 'success', message: 'Documento generado para ' . $cuenta->nombreBanco() . ' ***' . $cuenta->ultimosDigitos() . '.');
    }

    // ── Fin de mes ───────────────────────────────────────────────────────────

    public function procesarFinDeMes(): void
    {
        $cuentas = BankAccount::where('activa', true)->get();

        foreach ($cuentas as $cuenta) {
            BankService::procesarFinDeMes($cuenta, now()->format('Y-m'));
        }

        $this->showFinMesConfirm = false;
        $this->dispatch('notify', type: 'success', message: 'Fin de mes procesado. Se aplicaron cuotas de manejo e intereses.');
    }

    // ── Sobregiro (Banco de Bogotá) ────────────────────────────────────────

    public bool   $showSobregiroModal   = false;
    public float  $sobregiroMontoSolicitado = 0;
    public string $sobregiroContexto    = ''; // descripción del pago que lo activó

    public function usarCupoAgil(float $montoRequerido, string $contexto = ''): void
    {
        $cuenta = BankAccount::where('bank', 'banco_bogota')
            ->where('account_type', 'corriente')
            ->where('activa', true)
            ->where('bloqueada', false)
            ->first();

        if (! $cuenta || ! $cuenta->tieneSobregiro()) {
            $this->dispatch('notify', type: 'error', message: 'No tienes Cupo Ágil disponible.');
            return;
        }

        $cupoDisponible = $cuenta->sobregiro_disponible - $cuenta->sobregiro_usado;
        if ($montoRequerido > $cupoDisponible) {
            $this->dispatch('notify', type: 'error',
                message: 'El monto supera el cupo disponible ($' . number_format($cupoDisponible, 0, ',', '.') . ').');
            return;
        }

        $this->sobregiroMontoSolicitado = $montoRequerido;
        $this->sobregiroContexto        = $contexto;
        $this->showSobregiroModal       = true;
    }

    public function confirmarSobregiro(): void
    {
        $cuenta = BankAccount::where('bank', 'banco_bogota')
            ->where('activa', true)
            ->where('bloqueada', false)
            ->first();

        if (! $cuenta) {
            return;
        }

        $cuenta->increment('sobregiro_usado', $this->sobregiroMontoSolicitado);
        $cuenta->decrement('saldo', $this->sobregiroMontoSolicitado);

        BankTransaction::create([
            'bank_account_id'   => $cuenta->id,
            'tipo'              => 'retiro',
            'valor'             => $this->sobregiroMontoSolicitado,
            'gmf'               => 0,
            'comision'          => 0,
            'saldo_despues'     => $cuenta->fresh()->saldo,
            'descripcion'       => 'Cupo Ágil — ' . ($this->sobregiroContexto ?: 'Uso de sobregiro'),
            'fecha_transaccion' => now()->toDateString(),
        ]);

        $this->showSobregiroModal = false;
        $this->dispatch('notify', type: 'warning',
            message: 'Cupo Ágil activado: $' . number_format($this->sobregiroMontoSolicitado, 0, ',', '.') . '. Genera intereses diarios del 0.1%.');
    }

    // ── Colores y estilos ────────────────────────────────────────────────────

    public static function colorClaseBanco(string $bank): string
    {
        return match ($bank) {
            'bancolombia'  => 'bg-blue-500',
            'davivienda'   => 'bg-red-500',
            'banco_bogota' => 'bg-green-600',
            default        => 'bg-gray-500',
        };
    }

    // ── Alertas automáticas ───────────────────────────────────────────────────

    public function getAlertasProperty(): array
    {
        $alertas = [];
        $cuentas = BankAccount::where('activa', true)->get();

        foreach ($cuentas as $cuenta) {
            $tag = $cuenta->nombreBanco() . '***' . $cuenta->ultimosDigitos();

            if ($cuenta->bloqueada) {
                $alertas[] = ['tipo' => 'error',   'mensaje' => "CUENTA BLOQUEADA — {$tag}: Saldo el sobregiro para operar."];
            } elseif ($cuenta->sobregiro_usado > 0) {
                $alertas[] = ['tipo' => 'error',   'mensaje' => "Sobregiro activo en {$tag}: $" . number_format($cuenta->sobregiro_usado, 0, ',', '.') . " — genera intereses diarios."];
            } elseif ($cuenta->saldo < 1_000_000 && $cuenta->saldo >= 0) {
                $alertas[] = ['tipo' => 'warning', 'mensaje' => "Saldo crítico en {$tag}: $" . number_format($cuenta->saldo, 0, ',', '.')];
            } elseif ($cuenta->saldo < 5_000_000) {
                $alertas[] = ['tipo' => 'warning', 'mensaje' => "Saldo bajo en {$tag}: $" . number_format($cuenta->saldo, 0, ',', '.')];
            }

            // Cuota de manejo próxima (último día del mes)
            if (now()->daysInMonth - now()->day <= 3) {
                $cuota = BankService::cuotaManejoConIva($cuenta->bank, $cuenta->account_type);
                $alertas[] = ['tipo' => 'info', 'mensaje' => "En " . (now()->daysInMonth - now()->day) . " días se cobra cuota de manejo en {$tag}: $" . number_format($cuota, 0, ',', '.')];
            }

            // Cheques pendientes > 30 días
            $chequesVencidos = BankCheck::where('bank_account_id', $cuenta->id)
                ->where('estado', 'emitido')
                ->where('fecha_emision', '<=', now()->subDays(30)->toDateString())
                ->count();
            if ($chequesVencidos > 0) {
                $alertas[] = ['tipo' => 'warning', 'mensaje' => "{$chequesVencidos} cheque(s) en {$tag} llevan más de 30 días sin cobrar."];
            }
        }

        return $alertas;
    }

    public function render()
    {
        return view('livewire.tenant.banco.index');
    }
}
