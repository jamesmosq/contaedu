<?php

namespace App\Livewire\Tenant\Banco;

use App\Models\Tenant\Account;
use App\Models\Tenant\BankAccount;
use App\Models\Tenant\BankCheck;
use App\Models\Tenant\BankDocument;
use App\Models\Tenant\BankTransaction;
use App\Models\Tenant\JournalEntry;
use App\Models\Tenant\JournalLine;
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
    public string $nuevoBanco = '';

    public string $nuevaTipoCuenta = 'corriente';

    public float $montoInicial = 0;

    public bool $showSegundaCuenta = false;

    // ── Transferencia entre cuentas propias ─────────────────────────────────
    public bool $showTransferenciaForm = false;

    public ?int $transf_origen_id = null;

    public ?int $transf_destino_id = null;

    public float $transf_monto = 0;

    // ── Emitir cheque ─────────────────────────────────────────────────────────
    public bool $showChequeForm = false;

    public string $cheque_beneficiario = '';

    public float $cheque_valor = 0;

    public ?int $cheque_cuenta_debito_id = null;

    public string $cheque_concepto = '';

    public string $cheque_fecha = '';

    // ── Fin de mes ───────────────────────────────────────────────────────────
    public bool $showFinMesConfirm = false;

    // URL de la página de banco (capturada en mount para evitar la URL del endpoint Livewire en updates)
    public string $bancoPageUrl = '';

    public function mount(): void
    {
        $principal = BankAccount::where('es_principal', true)->where('activa', true)->first();
        $this->cuentaActivaId = $principal?->id;
        $this->bancoPageUrl = url()->current();
        $this->cheque_fecha = now()->toDateString();
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
            $nuevaCuenta = DB::transaction(function () {
                $cuenta = BankAccount::create([
                    'bank' => $this->nuevoBanco,
                    'account_number' => BankService::generarNumeroCuenta($this->nuevoBanco),
                    'account_type' => $this->nuevaTipoCuenta,
                    'saldo' => $this->montoInicial,
                    'sobregiro_disponible' => $this->nuevoBanco === 'banco_bogota' && $this->nuevaTipoCuenta === 'corriente' ? 5_000_000 : 0,
                    'sobregiro_usado' => 0,
                    'es_principal' => true,
                    'activa' => true,
                    'bloqueada' => false,
                    'cheques_disponibles' => $this->nuevaTipoCuenta === 'corriente' ? 30 : null,
                    'cheques_emitidos' => 0,
                    'fecha_apertura' => now()->toDateString(),
                ]);

                $tx = BankTransaction::create([
                    'bank_account_id' => $cuenta->id,
                    'tipo' => 'consignacion',
                    'valor' => $this->montoInicial,
                    'gmf' => 0,
                    'comision' => 0,
                    'saldo_despues' => $this->montoInicial,
                    'descripcion' => 'Consignación inicial — apertura de cuenta bancaria',
                    'fecha_transaccion' => now()->toDateString(),
                ]);

                BankService::generarAsientoBancario($tx, $cuenta);

                return $cuenta;
            });

            $this->showSegundaCuenta = false;
            $this->cuentaActivaId = $nuevaCuenta->id;
            $this->tab = 'movimientos';

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
        $gmf = BankService::calcularGmf('transferencia_salida', $this->montoInicial);
        $totalCargo = $this->montoInicial + $comisionAch + $gmf;

        if ($cuentaOrigen->saldo < $totalCargo) {
            $this->dispatch('notify', type: 'error', message: 'Saldo insuficiente para cubrir el monto + comisiones (ACH: $'.number_format($comisionAch).' + GMF: $'.number_format($gmf).').');

            return;
        }

        // Crear cuenta + movimientos en una transacción atómica
        $nuevaCuenta = DB::transaction(function () use ($cuentaOrigen, $comisionAch, $gmf, $totalCargo) {
            $cuenta = BankAccount::create([
                'bank' => $this->nuevoBanco,
                'account_number' => BankService::generarNumeroCuenta($this->nuevoBanco),
                'account_type' => $this->nuevaTipoCuenta,
                'saldo' => $this->montoInicial,
                'sobregiro_disponible' => $this->nuevoBanco === 'banco_bogota' && $this->nuevaTipoCuenta === 'corriente' ? 5_000_000 : 0,
                'sobregiro_usado' => 0,
                'es_principal' => false,
                'activa' => true,
                'bloqueada' => false,
                'cheques_disponibles' => $this->nuevaTipoCuenta === 'corriente' ? 30 : null,
                'cheques_emitidos' => 0,
                'fecha_apertura' => now()->toDateString(),
            ]);

            $cuentaOrigen->decrement('saldo', $totalCargo);
            $txSalida = BankTransaction::create([
                'bank_account_id' => $cuentaOrigen->id,
                'tipo' => 'transferencia_salida',
                'valor' => $this->montoInicial,
                'gmf' => $gmf,
                'comision' => $comisionAch,
                'saldo_despues' => $cuentaOrigen->fresh()->saldo,
                'descripcion' => 'Apertura cuenta '.$cuenta->nombreBanco().' '.$this->nuevaTipoCuenta,
                'banco_destino' => $this->nuevoBanco,
                'cuenta_destino' => $cuenta->account_number,
                'fecha_transaccion' => now()->toDateString(),
            ]);

            BankService::generarAsientoBancario($txSalida, $cuentaOrigen, $cuenta);

            BankTransaction::create([
                'bank_account_id' => $cuenta->id,
                'tipo' => 'consignacion',
                'valor' => $this->montoInicial,
                'gmf' => 0,
                'comision' => 0,
                'saldo_despues' => $this->montoInicial,
                'descripcion' => 'Consignación inicial apertura de cuenta',
                'fecha_transaccion' => now()->toDateString(),
            ]);

            return $cuenta;
        });

        $this->showSegundaCuenta = false;
        $this->cuentaActivaId = $nuevaCuenta->id;
        $this->tab = 'movimientos';

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

        $origen = BankAccount::find($this->transf_origen_id);
        $destino = BankAccount::find($this->transf_destino_id);

        if (! $origen || ! $destino) {
            return;
        }

        $ach = BankService::costoAch($origen->bank, $destino->bank);
        $gmf = BankService::calcularGmf('transferencia_salida', $this->transf_monto);
        $cargo = $this->transf_monto + $gmf + $ach;

        if ($origen->saldoDisponible() < $cargo) {
            $this->dispatch('notify', type: 'error',
                message: 'Saldo insuficiente. Necesitas $'.number_format($cargo, 0, ',', '.').' (incluye GMF $'.number_format($gmf, 0, ',', '.').($ach > 0 ? ' + ACH $'.number_format($ach, 0, ',', '.') : '').')');

            return;
        }

        DB::transaction(function () use ($origen, $destino, $ach, $gmf, $cargo) {
            $origen->decrement('saldo', $cargo);
            $txSalida = BankTransaction::create([
                'bank_account_id' => $origen->id,
                'tipo' => 'transferencia_salida',
                'valor' => $this->transf_monto,
                'gmf' => $gmf,
                'comision' => $ach,
                'saldo_despues' => $origen->fresh()->saldo,
                'descripcion' => 'Transferencia a '.$destino->nombreBanco().'***'.$destino->ultimosDigitos(),
                'banco_destino' => $destino->bank,
                'cuenta_destino' => $destino->account_number,
                'fecha_transaccion' => now()->toDateString(),
            ]);

            BankService::generarAsientoBancario($txSalida, $origen, $destino);

            $destino->increment('saldo', $this->transf_monto);
            BankTransaction::create([
                'bank_account_id' => $destino->id,
                'tipo' => 'transferencia_entrada',
                'valor' => $this->transf_monto,
                'gmf' => 0,
                'comision' => 0,
                'saldo_despues' => $destino->fresh()->saldo,
                'descripcion' => 'Transferencia desde '.$origen->nombreBanco().'***'.$origen->ultimosDigitos(),
                'fecha_transaccion' => now()->toDateString(),
            ]);
        });

        $this->showTransferenciaForm = false;
        $this->reset(['transf_origen_id', 'transf_destino_id', 'transf_monto']);
        $this->dispatch('notify', type: 'success', message: 'Transferencia realizada. GMF: $'.number_format($gmf, 0, ',', '.').($ach > 0 ? ' | ACH: $'.number_format($ach, 0, ',', '.') : ''));
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
        if (! $this->cheque_cuenta_debito_id) {
            $this->dispatch('notify', type: 'error', message: 'Selecciona la cuenta contable a debitar.');

            return;
        }

        $gmf = BankService::calcularGmf('cheque', $this->cheque_valor);
        $cargo = $this->cheque_valor + $gmf;

        if ($cuenta->saldoDisponible() < $cargo) {
            $this->dispatch('notify', type: 'error',
                message: 'Saldo insuficiente para emitir el cheque (incluye GMF $'.number_format($gmf, 0, ',', '.').').');

            return;
        }

        $numeroCheque = str_pad(($cuenta->cheques_emitidos + 1), 4, '0', STR_PAD_LEFT);
        $fecha = $this->cheque_fecha ?: now()->toDateString();
        $descripcion = 'Cheque #'.$numeroCheque.' — '.$this->cheque_beneficiario.($this->cheque_concepto ? ' — '.$this->cheque_concepto : '');

        DB::transaction(function () use ($cuenta, $gmf, $cargo, $numeroCheque, $fecha, $descripcion) {
            $cuenta->decrement('saldo', $cargo);
            $cuenta->increment('cheques_emitidos');

            $check = BankCheck::create([
                'bank_account_id' => $cuenta->id,
                'numero_cheque' => $numeroCheque,
                'beneficiario' => $this->cheque_beneficiario,
                'valor' => $this->cheque_valor,
                'fecha_emision' => $fecha,
                'estado' => 'emitido',
            ]);

            BankTransaction::create([
                'bank_account_id' => $cuenta->id,
                'tipo' => 'cheque',
                'valor' => $this->cheque_valor,
                'gmf' => $gmf,
                'comision' => 0,
                'saldo_despues' => $cuenta->fresh()->saldo,
                'descripcion' => $descripcion,
                'referencia' => $numeroCheque,
                'fecha_transaccion' => $fecha,
            ]);

            // Asiento contable: DR cuenta_debito | CR 1110 Bancos
            $bancoAccount = Account::where('code', 'like', '1110%')->where('level', '>=', 3)->first();
            if ($bancoAccount && $this->cheque_cuenta_debito_id) {
                $entry = JournalEntry::create([
                    'modo' => 'real',
                    'date' => $fecha,
                    'reference' => 'CHQ-'.$numeroCheque,
                    'description' => $descripcion,
                    'document_type' => 'cheque',
                    'document_id' => $check->id,
                    'auto_generated' => true,
                ]);

                JournalLine::create([
                    'journal_entry_id' => $entry->id,
                    'account_id' => $this->cheque_cuenta_debito_id,
                    'debit' => $this->cheque_valor,
                    'credit' => 0,
                    'description' => 'Cheque #'.$numeroCheque.' — '.$this->cheque_beneficiario,
                ]);

                JournalLine::create([
                    'journal_entry_id' => $entry->id,
                    'account_id' => $bancoAccount->id,
                    'debit' => 0,
                    'credit' => $this->cheque_valor,
                    'description' => 'Banco — cheque emitido #'.$numeroCheque,
                ]);

                $check->update(['journal_entry_id' => $entry->id]);
            }
        });

        $valor = $this->cheque_valor;
        $this->showChequeForm = false;
        $this->reset(['cheque_beneficiario', 'cheque_valor', 'cheque_cuenta_debito_id', 'cheque_concepto']);
        $this->cheque_fecha = now()->toDateString();
        $this->dispatch('notify', type: 'success', message: 'Cheque #'.$numeroCheque.' emitido por $'.number_format($valor, 0, ',', '.'));
    }

    // ── Anular cheque ─────────────────────────────────────────────────────────

    public function anularCheque(int $id): void
    {
        $check = BankCheck::with(['bankAccount', 'journalEntry.lines'])->findOrFail($id);

        if ($check->estado !== 'emitido') {
            $this->dispatch('notify', type: 'error', message: 'Solo se pueden anular cheques en estado Emitido.');

            return;
        }

        DB::transaction(function () use ($check) {
            // Revertir saldo bancario (sin GMF, ya cobrado)
            $check->bankAccount->increment('saldo', $check->valor);

            BankTransaction::create([
                'bank_account_id' => $check->bank_account_id,
                'tipo' => 'anulacion_cheque',
                'valor' => $check->valor,
                'gmf' => 0,
                'comision' => 0,
                'saldo_despues' => $check->bankAccount->fresh()->saldo,
                'descripcion' => 'Anulación cheque #'.$check->numero_cheque.' — '.$check->beneficiario,
                'referencia' => $check->numero_cheque,
                'fecha_transaccion' => now()->toDateString(),
            ]);

            // Reverso contable
            if ($check->journalEntry) {
                $reversal = JournalEntry::create([
                    'modo' => 'real',
                    'date' => now()->toDateString(),
                    'reference' => 'AN-CHQ-'.$check->numero_cheque,
                    'description' => 'Anulación cheque #'.$check->numero_cheque.' — '.$check->beneficiario,
                    'document_type' => 'cheque_anulado',
                    'document_id' => $check->id,
                    'auto_generated' => true,
                ]);

                foreach ($check->journalEntry->lines as $line) {
                    JournalLine::create([
                        'journal_entry_id' => $reversal->id,
                        'account_id' => $line->account_id,
                        'debit' => $line->credit,
                        'credit' => $line->debit,
                        'description' => 'Reverso: '.$line->description,
                    ]);
                }
            }

            $check->update(['estado' => 'anulado']);
        });

        $this->dispatch('notify', type: 'success', message: 'Cheque #'.$check->numero_cheque.' anulado correctamente.');
    }

    // ── Marcar cheque cobrado ─────────────────────────────────────────────────

    public function marcarCobrado(int $id): void
    {
        $check = BankCheck::findOrFail($id);

        if ($check->estado !== 'emitido') {
            $this->dispatch('notify', type: 'error', message: 'Solo se pueden marcar como cobrados los cheques en estado Emitido.');

            return;
        }

        $check->update([
            'estado' => 'cobrado',
            'fecha_cobro' => now()->toDateString(),
        ]);

        $this->dispatch('notify', type: 'success', message: 'Cheque #'.$check->numero_cheque.' marcado como cobrado.');
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
            $this->dispatch('notify', type: 'error', message: 'El paz y salvo solo se puede solicitar cuando el saldo de '.$cuenta->nombreBanco().' sea $0.');

            return;
        }

        BankDocument::create([
            'bank_account_id' => $cuenta->id,
            'tipo' => $tipo,
            'pdf_path' => '',
            'generado_at' => now(),
        ]);

        $this->dispatch('notify', type: 'success', message: 'Documento generado para '.$cuenta->nombreBanco().' ***'.$cuenta->ultimosDigitos().'.');
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

    public bool $showSobregiroModal = false;

    public float $sobregiroMontoSolicitado = 0;

    public string $sobregiroContexto = ''; // descripción del pago que lo activó

    // ── Pagar sobregiro ──────────────────────────────────────────────────────
    public bool $showPagarSobregiroModal = false;

    public float $pagarSobregiroMonto = 0;

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
                message: 'El monto supera el cupo disponible ($'.number_format($cupoDisponible, 0, ',', '.').').');

            return;
        }

        $this->sobregiroMontoSolicitado = $montoRequerido;
        $this->sobregiroContexto = $contexto;
        $this->showSobregiroModal = true;
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

        DB::transaction(function () use ($cuenta) {
            $cuenta->increment('sobregiro_usado', $this->sobregiroMontoSolicitado);
            $cuenta->decrement('saldo', $this->sobregiroMontoSolicitado);

            $tx = BankTransaction::create([
                'bank_account_id' => $cuenta->id,
                'tipo' => 'retiro',
                'valor' => $this->sobregiroMontoSolicitado,
                'gmf' => 0,
                'comision' => 0,
                'saldo_despues' => $cuenta->fresh()->saldo,
                'descripcion' => 'Cupo Ágil — '.($this->sobregiroContexto ?: 'Uso de sobregiro'),
                'fecha_transaccion' => now()->toDateString(),
            ]);

            BankService::generarAsientoBancario($tx, $cuenta);
        });

        $this->showSobregiroModal = false;
        $this->dispatch('notify', type: 'warning',
            message: 'Cupo Ágil activado: $'.number_format($this->sobregiroMontoSolicitado, 0, ',', '.').'. Genera intereses diarios del 0.1%.');
    }

    public function pagarSobregiro(): void
    {
        $cuenta = BankAccount::where('bank', 'banco_bogota')
            ->where('activa', true)
            ->where('sobregiro_usado', '>', 0)
            ->first();

        if (! $cuenta) {
            $this->dispatch('notify', type: 'error', message: 'No se encontró una cuenta con sobregiro activo.');

            return;
        }

        if ($this->pagarSobregiroMonto <= 0) {
            $this->dispatch('notify', type: 'error', message: 'Ingresa un monto mayor a $0.');

            return;
        }

        try {
            BankService::pagarSobregiro($cuenta, $this->pagarSobregiroMonto);
        } catch (\RuntimeException $e) {
            $this->dispatch('notify', type: 'error', message: $e->getMessage());

            return;
        }

        $montoConfirmado = $this->pagarSobregiroMonto;
        $sobroUsadoNuevo = $cuenta->fresh()->sobregiro_usado;

        $this->showPagarSobregiroModal = false;
        $this->pagarSobregiroMonto = 0;

        $mensaje = 'Pago de $'.number_format($montoConfirmado, 0, ',', '.').' al Cupo Ágil registrado.';
        if ($sobroUsadoNuevo <= 0) {
            $mensaje .= ' Sobregiro saldado — cuenta desbloqueada.';
        }

        $this->dispatch('notify', type: 'success', message: $mensaje);
    }

    // ── Colores y estilos ────────────────────────────────────────────────────

    public static function colorClaseBanco(string $bank): string
    {
        return match ($bank) {
            'bancolombia' => 'bg-blue-500',
            'davivienda' => 'bg-red-500',
            'banco_bogota' => 'bg-green-600',
            default => 'bg-gray-500',
        };
    }

    // ── Alertas automáticas ───────────────────────────────────────────────────

    public function getAlertasProperty(): array
    {
        $alertas = [];
        $cuentas = BankAccount::where('activa', true)->get();

        foreach ($cuentas as $cuenta) {
            $tag = $cuenta->nombreBanco().'***'.$cuenta->ultimosDigitos();

            if ($cuenta->bloqueada) {
                $alertas[] = ['tipo' => 'error',   'mensaje' => "CUENTA BLOQUEADA — {$tag}: Saldo el sobregiro para operar."];
            } elseif ($cuenta->sobregiro_usado > 0) {
                $alertas[] = ['tipo' => 'error',   'mensaje' => "Sobregiro activo en {$tag}: $".number_format($cuenta->sobregiro_usado, 0, ',', '.').' — genera intereses diarios.'];
            } elseif ($cuenta->saldo < 1_000_000 && $cuenta->saldo >= 0) {
                $alertas[] = ['tipo' => 'warning', 'mensaje' => "Saldo crítico en {$tag}: $".number_format($cuenta->saldo, 0, ',', '.')];
            } elseif ($cuenta->saldo < 5_000_000) {
                $alertas[] = ['tipo' => 'warning', 'mensaje' => "Saldo bajo en {$tag}: $".number_format($cuenta->saldo, 0, ',', '.')];
            }

            // Cuota de manejo próxima (último día del mes)
            if (now()->daysInMonth - now()->day <= 3) {
                $cuota = BankService::cuotaManejoConIva($cuenta->bank, $cuenta->account_type);
                $alertas[] = ['tipo' => 'info', 'mensaje' => 'En '.(now()->daysInMonth - now()->day)." días se cobra cuota de manejo en {$tag}: $".number_format($cuota, 0, ',', '.')];
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
        $accounts = Account::orderBy('code')->get();

        return view('livewire.tenant.banco.index', compact('accounts'));
    }
}
