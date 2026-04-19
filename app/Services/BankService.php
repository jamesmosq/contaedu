<?php

namespace App\Services;

use App\Models\Tenant\Account;
use App\Models\Tenant\BankAccount;
use App\Models\Tenant\BankTransaction;
use App\Models\Tenant\JournalEntry;
use App\Models\Tenant\JournalLine;
use Illuminate\Support\Facades\DB;

class BankService
{
    // ─── Tarifas por banco ────────────────────────────────────────────────────

    /**
     * Cuota de manejo mensual (sin IVA) por banco y tipo de cuenta.
     */
    public static function cuotaManejo(string $bank, string $accountType): float
    {
        return match (true) {
            $bank === 'bancolombia' && $accountType === 'corriente' => 15_900,
            $bank === 'bancolombia' && $accountType === 'ahorros' => 8_900,
            $bank === 'davivienda' && $accountType === 'corriente' => 12_800,
            $bank === 'davivienda' && $accountType === 'ahorros' => 6_400,
            $bank === 'banco_bogota' && $accountType === 'corriente' => 14_500,
            $bank === 'banco_bogota' && $accountType === 'ahorros' => 7_200,
            default => 0,
        };
    }

    /**
     * IVA de la cuota de manejo (19%).
     */
    public static function cuotaManejoConIva(string $bank, string $accountType): float
    {
        return round(static::cuotaManejo($bank, $accountType) * 1.19, 0);
    }

    /**
     * Costo de transferencia ACH entre bancos diferentes.
     */
    public static function costoAch(string $bankOrigen, string $bankDestino): float
    {
        if ($bankOrigen === $bankDestino) {
            return 0;
        }

        return match ($bankOrigen) {
            'bancolombia' => 4_200,
            'davivienda' => 3_800,
            'banco_bogota' => 4_500,
            default => 4_200,
        };
    }

    /**
     * Tasa de interés mensual de ahorros por banco.
     */
    public static function tasaInteresAhorros(string $bank): float
    {
        return match ($bank) {
            'bancolombia' => 0.003,  // 0.3%
            'davivienda' => 0.0025, // 0.25%
            'banco_bogota' => 0.0035, // 0.35%
            default => 0.003,
        };
    }

    // ─── GMF ─────────────────────────────────────────────────────────────────

    /**
     * Calcula el GMF 4x1000 para una transacción.
     * Solo aplica en retiros, transferencias salida, cheques y pagos a proveedor.
     */
    public static function calcularGmf(string $tipo, float $valor): float
    {
        $tiposGravados = [
            'retiro',
            'transferencia_salida',
            'cheque',
            'pago_proveedor',
        ];

        if (! in_array($tipo, $tiposGravados)) {
            return 0;
        }

        return round($valor * 0.004);
    }

    // ─── Inicialización de cuenta ─────────────────────────────────────────────

    /**
     * Asigna un banco aleatorio y crea la cuenta principal para un tenant nuevo.
     * Se llama desde AutoMigrateTenant después de seedCapitalInicial().
     */
    public static function crearCuentaPrincipal(float $saldoInicial = 100_000_000): BankAccount
    {
        $bank = collect(['bancolombia', 'davivienda', 'banco_bogota'])->random();

        return BankAccount::create([
            'bank' => $bank,
            'account_number' => static::generarNumeroCuenta($bank),
            'account_type' => 'corriente',
            'saldo' => $saldoInicial,
            'sobregiro_disponible' => $bank === 'banco_bogota' ? 5_000_000 : 0,
            'sobregiro_usado' => 0,
            'es_principal' => true,
            'activa' => true,
            'bloqueada' => false,
            'cheques_disponibles' => 30,
            'cheques_emitidos' => 0,
            'fecha_apertura' => now()->toDateString(),
        ]);
    }

    /**
     * Genera un número de cuenta simulado con el formato real de cada banco.
     * Bancolombia: XXX-XXXXXX-XX
     * Davivienda:  XXXX-XXXX-XXXX
     * Banco Bogotá: XXX-XXXXX-X
     */
    public static function generarNumeroCuenta(string $bank): string
    {
        return match ($bank) {
            'bancolombia' => sprintf(
                '%03d-%06d-%02d',
                random_int(100, 999),
                random_int(100000, 999999),
                random_int(10, 99)
            ),
            'davivienda' => sprintf(
                '%04d-%04d-%04d',
                random_int(1000, 9999),
                random_int(1000, 9999),
                random_int(1000, 9999)
            ),
            'banco_bogota' => sprintf(
                '%03d-%05d-%01d',
                random_int(100, 999),
                random_int(10000, 99999),
                random_int(0, 9)
            ),
            default => (string) random_int(1000000000, 9999999999),
        };
    }

    // ─── Operaciones bancarias ────────────────────────────────────────────────

    /**
     * Registra una transacción en la cuenta y genera el asiento contable.
     * Actualiza el saldo de la cuenta automáticamente.
     *
     * @param  float  $valor  Valor base de la transacción (sin GMF ni comisión)
     * @param  array  $extra  Campos adicionales opcionales
     */
    public static function registrarTransaccion(
        BankAccount $cuenta,
        string $tipo,
        float $valor,
        string $descripcion,
        array $extra = []
    ): BankTransaction {
        $gmf = static::calcularGmf($tipo, $valor);
        $comision = $extra['comision'] ?? 0;
        $totalCargo = $valor + $gmf + $comision;

        // Actualizar saldo
        if (static::esCargo($tipo)) {
            $nuevoSaldo = $cuenta->saldo - $totalCargo;
        } else {
            $nuevoSaldo = $cuenta->saldo + $valor;
        }

        $cuenta->update(['saldo' => $nuevoSaldo]);

        return BankTransaction::create(array_merge([
            'bank_account_id' => $cuenta->id,
            'tipo' => $tipo,
            'valor' => $valor,
            'gmf' => $gmf,
            'comision' => $comision,
            'saldo_despues' => $nuevoSaldo,
            'descripcion' => $descripcion,
            'fecha_transaccion' => $extra['fecha'] ?? now()->toDateString(),
        ], $extra));
    }

    /**
     * Determina si un tipo de transacción es un cargo (salida de dinero).
     */
    public static function esCargo(string $tipo): bool
    {
        return in_array($tipo, [
            'retiro',
            'transferencia_salida',
            'cheque',
            'pago_proveedor',
            'cuota_manejo',
            'intereses_sobregiro',
            'gmf',
            'comision_ach',
            'sancion_cheque_devuelto',
            'nota_debito',
        ]);
    }

    // ─── Fin de mes ───────────────────────────────────────────────────────────

    /**
     * Procesa el cierre de mes para una cuenta bancaria:
     * - Cobra cuota de manejo (con IVA)
     * - Abona intereses si es cuenta de ahorros
     * - Cobra intereses de sobregiro si aplica
     *
     * Genera asientos contables para cada movimiento.
     */
    public static function procesarFinDeMes(BankAccount $cuenta, string $periodo): void
    {
        DB::transaction(function () use ($cuenta, $periodo) {
            // 1. Cuota de manejo
            $cuota = static::cuotaManejoConIva($cuenta->bank, $cuenta->account_type);
            if ($cuota > 0) {
                static::aplicarCuotaManejo($cuenta, $cuota, $periodo);
            }

            // 2. Intereses de ahorros
            if ($cuenta->account_type === 'ahorros' && $cuenta->saldo > 0) {
                $intereses = round($cuenta->saldo * static::tasaInteresAhorros($cuenta->bank), 2);
                if ($intereses > 0) {
                    static::aplicarInteresesAhorros($cuenta, $intereses, $periodo);
                }
            }

            // 3. Intereses de sobregiro + contador de períodos
            if ($cuenta->sobregiro_usado > 0) {
                $interesSobregiro = round($cuenta->sobregiro_usado * 0.001 * 30, 2); // 0.1% diario × 30 días
                static::aplicarInteresSobregiro($cuenta, $interesSobregiro, $periodo);

                // Incrementar contador de períodos con sobregiro sin pagar
                $nuevoPeriodos = ($cuenta->sobregiro_periodos ?? 0) + 1;
                $cuenta->update(['sobregiro_periodos' => $nuevoPeriodos]);

                // Bloquear si lleva 2 períodos consecutivos con sobregiro
                if ($nuevoPeriodos >= 2) {
                    $cuenta->update(['bloqueada' => true]);
                }
            } else {
                // Sin sobregiro pendiente: reiniciar contador
                if (($cuenta->sobregiro_periodos ?? 0) > 0) {
                    $cuenta->update(['sobregiro_periodos' => 0]);
                }
            }
        });
    }

    // ─── Pago de sobregiro ────────────────────────────────────────────────────

    /**
     * Paga parcial o totalmente el Cupo Ágil utilizado.
     * Valida monto, actualiza saldo/sobregiro en una transacción atómica
     * y registra el movimiento. Si el sobregiro queda en 0 desbloquea la cuenta.
     *
     * @throws \RuntimeException con mensaje pedagógico en caso de error de negocio
     */
    public static function pagarSobregiro(BankAccount $cuenta, float $monto): BankTransaction
    {
        if ($monto <= 0) {
            throw new \RuntimeException('El monto a pagar debe ser mayor a $0.');
        }

        if ($monto > $cuenta->sobregiro_usado) {
            throw new \RuntimeException(
                'El monto ($'.number_format($monto, 0, ',', '.').') supera el sobregiro utilizado ($'.number_format($cuenta->sobregiro_usado, 0, ',', '.').').'
            );
        }

        if ($cuenta->saldo < $monto) {
            throw new \RuntimeException(
                'Saldo insuficiente. Necesitas $'.number_format($monto, 0, ',', '.').' y tienes $'.number_format($cuenta->saldo, 0, ',', '.').'.'
            );
        }

        return DB::transaction(function () use ($cuenta, $monto) {
            $cuenta->decrement('saldo', $monto);
            $cuenta->decrement('sobregiro_usado', $monto);
            $cuenta->increment('sobregiro_disponible', $monto);

            $sobreroGiroUsadoNuevo = $cuenta->fresh()->sobregiro_usado;

            if ($sobreroGiroUsadoNuevo <= 0) {
                $cuenta->update([
                    'sobregiro_periodos' => 0,
                    'bloqueada' => false,
                ]);
            }

            return BankTransaction::create([
                'bank_account_id' => $cuenta->id,
                'tipo' => 'pago_sobregiro',
                'valor' => $monto,
                'gmf' => 0,
                'comision' => 0,
                'saldo_despues' => $cuenta->fresh()->saldo,
                'descripcion' => 'Pago Cupo Ágil — abono a sobregiro utilizado',
                'fecha_transaccion' => now()->toDateString(),
            ]);
        });
    }

    // ─── Asientos contables bancarios ─────────────────────────────────────────

    /**
     * Genera el asiento contable para una transacción bancaria operativa.
     * Aplica solo a: retiro, consignacion, transferencia_salida.
     * Idempotente: si journal_entry_id ya está asignado no hace nada.
     *
     * retiro          → DR 1105 Caja       / CR 1110 Bancos  (efectivo sale del banco)
     * consignacion    → DR 1110 Bancos     / CR 1105 Caja    (efectivo entra al banco)
     * transferencia_salida → DR 1110 Bancos (desc) / CR 1110 Bancos (orig), neto $0
     */
    public static function generarAsientoBancario(
        BankTransaction $tx,
        BankAccount $cuenta,
        ?BankAccount $cuentaDestino = null
    ): ?JournalEntry {
        if (! empty($tx->journal_entry_id)) {
            return null;
        }

        $bancos = Account::where('code', 'like', '1110%')->where('level', '>=', 3)->first();
        $caja = Account::where('code', 'like', '1105%')->where('level', '>=', 3)->first();

        if (! $bancos || ! $caja) {
            return null;
        }

        $lines = match ($tx->tipo) {
            'retiro' => [
                ['account_id' => $caja->id,   'debit' => $tx->valor, 'credit' => 0,          'description' => 'Retiro efectivo — '.$cuenta->nombreBanco()],
                ['account_id' => $bancos->id,  'debit' => 0,          'credit' => $tx->valor, 'description' => 'Retiro banco '.$cuenta->nombreBanco()],
            ],
            'consignacion' => [
                ['account_id' => $bancos->id, 'debit' => $tx->valor, 'credit' => 0,          'description' => 'Consignación '.$cuenta->nombreBanco()],
                ['account_id' => $caja->id,   'debit' => 0,          'credit' => $tx->valor, 'description' => 'Salida de caja — consignación'],
            ],
            'transferencia_salida' => [
                ['account_id' => $bancos->id, 'debit' => $tx->valor, 'credit' => 0,          'description' => 'Transferencia a '.($cuentaDestino?->nombreBanco() ?? 'cuenta destino')],
                ['account_id' => $bancos->id, 'debit' => 0,          'credit' => $tx->valor, 'description' => 'Transferencia desde '.$cuenta->nombreBanco()],
            ],
            default => null,
        };

        if ($lines === null) {
            return null;
        }

        $entry = JournalEntry::create([
            'date' => $tx->fecha_transaccion,
            'reference' => 'BNK-'.strtoupper(substr($tx->tipo, 0, 3)).'-'.str_pad($tx->id, 5, '0', STR_PAD_LEFT),
            'description' => $tx->descripcion,
            'document_type' => 'banco_'.$tx->tipo,
            'document_id' => $tx->id,
            'auto_generated' => true,
        ]);

        foreach ($lines as $line) {
            JournalLine::create(array_merge($line, ['journal_entry_id' => $entry->id]));
        }

        $tx->update(['journal_entry_id' => $entry->id]);

        return $entry;
    }

    /**
     * Genera asiento contable para cuota de manejo bancaria.
     * Db 530510 Comisiones bancarias | Cr 1110 Bancos
     */
    private static function aplicarCuotaManejo(BankAccount $cuenta, float $monto, string $periodo): void
    {
        $comisiones = Account::where('code', 'like', '5305%')->where('level', '>=', 3)->first();
        $bancos = Account::where('code', 'like', '1110%')->where('level', '>=', 3)->first();

        if (! $comisiones || ! $bancos) {
            return;
        }

        $entry = JournalEntry::create([
            'date' => now()->toDateString(),
            'reference' => 'BNK-CM-'.$periodo,
            'description' => 'Cuota de manejo '.$cuenta->nombreBanco().' '.$periodo,
            'document_type' => 'cuota_manejo_banco',
            'auto_generated' => true,
        ]);

        JournalLine::create(['journal_entry_id' => $entry->id, 'account_id' => $comisiones->id, 'debit' => $monto, 'credit' => 0,     'description' => 'Cuota manejo '.$cuenta->nombreBanco()]);
        JournalLine::create(['journal_entry_id' => $entry->id, 'account_id' => $bancos->id,     'debit' => 0,      'credit' => $monto, 'description' => 'Cuota manejo '.$cuenta->nombreBanco()]);

        $cuenta->decrement('saldo', $monto);

        BankTransaction::create([
            'bank_account_id' => $cuenta->id,
            'tipo' => 'cuota_manejo',
            'valor' => $monto,
            'gmf' => 0,
            'comision' => 0,
            'saldo_despues' => $cuenta->fresh()->saldo,
            'descripcion' => 'Cuota de manejo '.$periodo,
            'journal_entry_id' => $entry->id,
            'fecha_transaccion' => now()->toDateString(),
        ]);
    }

    /**
     * Genera asiento para intereses de cuenta de ahorros.
     * Db 1110 Bancos | Cr 4210 Intereses
     */
    private static function aplicarInteresesAhorros(BankAccount $cuenta, float $monto, string $periodo): void
    {
        $bancos = Account::where('code', 'like', '1110%')->where('level', '>=', 3)->first();
        $intereses = Account::where('code', 'like', '4210%')->where('level', '>=', 3)->first();

        if (! $bancos || ! $intereses) {
            return;
        }

        $entry = JournalEntry::create([
            'date' => now()->toDateString(),
            'reference' => 'BNK-INT-'.$periodo,
            'description' => 'Intereses ahorros '.$cuenta->nombreBanco().' '.$periodo,
            'document_type' => 'intereses_banco',
            'auto_generated' => true,
        ]);

        JournalLine::create(['journal_entry_id' => $entry->id, 'account_id' => $bancos->id,    'debit' => $monto, 'credit' => 0,     'description' => 'Intereses ahorros '.$cuenta->nombreBanco()]);
        JournalLine::create(['journal_entry_id' => $entry->id, 'account_id' => $intereses->id, 'debit' => 0,      'credit' => $monto, 'description' => 'Intereses ahorros '.$periodo]);

        $cuenta->increment('saldo', $monto);

        BankTransaction::create([
            'bank_account_id' => $cuenta->id,
            'tipo' => 'intereses_ahorros',
            'valor' => $monto,
            'gmf' => 0,
            'comision' => 0,
            'saldo_despues' => $cuenta->fresh()->saldo,
            'descripcion' => 'Intereses ahorros '.$periodo,
            'journal_entry_id' => $entry->id,
            'fecha_transaccion' => now()->toDateString(),
        ]);
    }

    /**
     * Genera asiento para intereses de sobregiro.
     * Db 530505 Intereses financieros | Cr 1110 Bancos
     */
    private static function aplicarInteresSobregiro(BankAccount $cuenta, float $monto, string $periodo): void
    {
        $interesesGasto = Account::where('code', 'like', '5305%')->where('level', '>=', 3)->first();
        $bancos = Account::where('code', 'like', '1110%')->where('level', '>=', 3)->first();

        if (! $interesesGasto || ! $bancos) {
            return;
        }

        $entry = JournalEntry::create([
            'date' => now()->toDateString(),
            'reference' => 'BNK-SOB-'.$periodo,
            'description' => 'Intereses sobregiro '.$cuenta->nombreBanco().' '.$periodo,
            'document_type' => 'intereses_sobregiro',
            'auto_generated' => true,
        ]);

        JournalLine::create(['journal_entry_id' => $entry->id, 'account_id' => $interesesGasto->id, 'debit' => $monto, 'credit' => 0,     'description' => 'Intereses sobregiro '.$cuenta->nombreBanco()]);
        JournalLine::create(['journal_entry_id' => $entry->id, 'account_id' => $bancos->id,         'debit' => 0,      'credit' => $monto, 'description' => 'Intereses sobregiro '.$periodo]);

        $cuenta->decrement('saldo', $monto);

        BankTransaction::create([
            'bank_account_id' => $cuenta->id,
            'tipo' => 'intereses_sobregiro',
            'valor' => $monto,
            'gmf' => 0,
            'comision' => 0,
            'saldo_despues' => $cuenta->fresh()->saldo,
            'descripcion' => 'Intereses sobregiro '.$periodo,
            'journal_entry_id' => $entry->id,
            'fecha_transaccion' => now()->toDateString(),
        ]);
    }
}
