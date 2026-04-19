<?php

namespace App\Services\FacturacionElectronica;

use App\Enums\EstadoFacturaEnum;
use App\Enums\EventoReceptorEnum;
use App\Events\FacturaAnulada;
use App\Events\FacturaValidada;
use App\Exceptions\AceptacionExpresaException;
use App\Exceptions\RangoAgotadoException;
use App\Exceptions\TransicionEstadoInvalidaException;
use App\Models\Tenant\FeEventoReceptor;
use App\Models\Tenant\FeFactura;
use App\Models\Tenant\FeNotaCredito;
use App\Models\Tenant\FeResolucion;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Orquestador principal del módulo de Facturación Electrónica.
 * Coordina ValidadorFacturaService, CufeService, XmlUblService y DianSimuladorService.
 */
class FacturaService
{
    public function __construct(
        private readonly ValidadorFacturaService $validador,
        private readonly CufeService $cufeService,
        private readonly CudeService $cudeService,
        private readonly XmlUblService $xmlService,
        private readonly DianSimuladorService $simulador,
    ) {}

    /**
     * Flujo completo: validar → asignar consecutivo → calcular CUFE → generar XML → enviar simulador.
     */
    public function emitir(FeFactura $factura): FeFactura
    {
        if (! $factura->estado->puedeTransicionarA(EstadoFacturaEnum::Generada)) {
            throw new TransicionEstadoInvalidaException($factura->estado->value, EstadoFacturaEnum::Generada->value);
        }

        return DB::transaction(function () use ($factura) {
            // 1. Cargar relaciones necesarias
            $factura->load(['resolucion', 'detalles']);

            // 2. Validación interna
            $resultado = $this->validador->validar($factura);
            if (! $resultado->esValido()) {
                throw new RuntimeException($resultado->primerError());
            }

            // 3. Asignar número consecutivo (lockForUpdate evita duplicados en concurrencia)
            $resolucion = FeResolucion::lockForUpdate()->find($factura->resolucion_id);

            if ($resolucion->rangoAgotado()) {
                throw new RangoAgotadoException($resolucion->numero_resolucion);
            }

            $numero = $resolucion->numero_actual;
            $factura->numero = $numero;
            $factura->numero_completo = ($resolucion->prefijo ? $resolucion->prefijo.'-' : '').str_pad($numero, 6, '0', STR_PAD_LEFT);
            $resolucion->increment('numero_actual');

            // 4. Calcular CUFE
            $factura->hora_emision = $factura->hora_emision ?? now();
            $factura->cufe = $this->cufeService->calcular($factura, $resolucion);

            // 5. Generar XML UBL 2.1
            $factura->xml_factura = $this->xmlService->generarFactura($factura);

            // 6. Cambiar estado a 'generada' y registrar evento
            $this->cambiarEstado($factura, EstadoFacturaEnum::Generada, 'sistema', 'XML y CUFE generados correctamente.');
            $factura->save();

            // 7. Marcar como 'enviada' (estado intermedio pedagógico: la factura viaja hacia la DIAN)
            $this->cambiarEstado($factura, EstadoFacturaEnum::Enviada, 'sistema', 'Factura enviada al simulador DIAN. Esperando respuesta de validación.');
            $factura->save();

            // 8. Enviar al simulador DIAN
            $respuesta = $this->simulador->validar($factura, $resolucion);

            // 9. Procesar respuesta
            if ($respuesta->aceptada) {
                $this->cambiarEstado($factura, EstadoFacturaEnum::Validada, 'simulador_dian', $respuesta->mensaje);
                $factura->codigo_respuesta_dian = $respuesta->codigoRespuesta;
                $factura->mensaje_dian = $respuesta->mensaje;
                $factura->fecha_validacion_dian = $respuesta->fechaValidacion;
                $factura->xml_application_response = $respuesta->xmlResponse;
                $factura->qr_data = "https://catalogo-vpfe.dian.gov.co/document/searchqr?documentkey={$factura->cufe}";
                $factura->save();

                FacturaValidada::dispatch($factura);
            } else {
                $this->cambiarEstado($factura, EstadoFacturaEnum::Rechazada, 'simulador_dian', $respuesta->mensaje);
                $factura->codigo_respuesta_dian = $respuesta->codigoRespuesta;
                $factura->mensaje_dian = $respuesta->mensaje;
                $factura->xml_application_response = $respuesta->xmlResponse;
                $factura->save();
            }

            return $factura;
        });
    }

    /**
     * Permite corregir y reenviar una factura rechazada.
     */
    public function reenviar(FeFactura $factura): FeFactura
    {
        if ($factura->estado !== EstadoFacturaEnum::Rechazada) {
            throw new TransicionEstadoInvalidaException($factura->estado->value, EstadoFacturaEnum::Generada->value);
        }

        return DB::transaction(function () use ($factura) {
            $factura->load(['resolucion', 'detalles']);

            $resultado = $this->validador->validar($factura);
            if (! $resultado->esValido()) {
                throw new RuntimeException($resultado->primerError());
            }

            // Recalcular CUFE (puede haber cambiado si se corrigieron datos)
            $factura->cufe = $this->cufeService->calcular($factura, $factura->resolucion);
            $factura->xml_factura = $this->xmlService->generarFactura($factura);

            $this->cambiarEstado($factura, EstadoFacturaEnum::Generada, 'usuario', 'Reenvío — XML y CUFE recalculados.');
            $factura->save();

            $this->cambiarEstado($factura, EstadoFacturaEnum::Enviada, 'sistema', 'Factura reenviada al simulador DIAN. Esperando respuesta de validación.');
            $factura->save();

            $respuesta = $this->simulador->validar($factura, $factura->resolucion);

            if ($respuesta->aceptada) {
                $this->cambiarEstado($factura, EstadoFacturaEnum::Validada, 'simulador_dian', $respuesta->mensaje);
                $factura->codigo_respuesta_dian = $respuesta->codigoRespuesta;
                $factura->mensaje_dian = $respuesta->mensaje;
                $factura->fecha_validacion_dian = $respuesta->fechaValidacion;
                $factura->xml_application_response = $respuesta->xmlResponse;
                $factura->qr_data = "https://catalogo-vpfe.dian.gov.co/document/searchqr?documentkey={$factura->cufe}";
                $factura->save();

                FacturaValidada::dispatch($factura);
            } else {
                $this->cambiarEstado($factura, EstadoFacturaEnum::Rechazada, 'simulador_dian', $respuesta->mensaje);
                $factura->codigo_respuesta_dian = $respuesta->codigoRespuesta;
                $factura->mensaje_dian = $respuesta->mensaje;
                $factura->xml_application_response = $respuesta->xmlResponse;
                $factura->save();
            }

            return $factura;
        });
    }

    /**
     * Devuelve una factura rechazada a borrador para que el usuario corrija los datos.
     */
    public function corregir(FeFactura $factura): FeFactura
    {
        if (! $factura->esRechazada()) {
            throw new TransicionEstadoInvalidaException($factura->estado->value, EstadoFacturaEnum::Borrador->value);
        }

        return DB::transaction(function () use ($factura) {
            $factura->cufe = null;
            $factura->xml_factura = null;
            $factura->xml_application_response = null;
            $factura->codigo_respuesta_dian = null;
            $factura->mensaje_dian = null;
            $factura->qr_data = null;
            $this->cambiarEstado($factura, EstadoFacturaEnum::Borrador, 'usuario', 'Factura devuelta a borrador para corrección.');
            $factura->save();

            return $factura;
        });
    }

    /**
     * Anula una factura validada mediante nota crédito de anulación.
     */
    public function anular(FeFactura $factura, string $motivo = 'Anulación de factura'): FeNotaCredito
    {
        if ($factura->estado !== EstadoFacturaEnum::Validada) {
            throw new TransicionEstadoInvalidaException($factura->estado->value, EstadoFacturaEnum::Anulada->value);
        }

        if ($factura->tieneAceptacionExpresa()) {
            throw new AceptacionExpresaException;
        }

        return DB::transaction(function () use ($factura, $motivo) {
            $resolucion = $factura->resolucion;

            $nota = FeNotaCredito::create([
                'factura_origen_id' => $factura->id,
                'resolucion_id' => $resolucion->id,
                'numero_completo' => ($resolucion->prefijo ? 'NC-'.$resolucion->prefijo.'-' : 'NC-').str_pad($factura->numero, 6, '0', STR_PAD_LEFT),
                'fecha_emision' => now()->toDateString(),
                'hora_emision' => now()->toTimeString(),
                'codigo_concepto' => '2', // Anulación de factura
                'descripcion_concepto' => $motivo,
                'subtotal' => $factura->subtotal,
                'valor_iva' => $factura->valor_iva,
                'total' => $factura->total,
                'estado' => 'generada',
                'user_id' => $factura->user_id,
            ]);

            // Calcular CUDE
            $nota->cude = $this->cudeService->calcular($nota, $resolucion);
            $nota->xml_nota = $this->xmlService->generarNota($nota);
            $nota->save();

            // Simular envío a DIAN
            $xmlResponse = $this->xmlService->generarApplicationResponse(true, '00', 'Nota crédito procesada correctamente.', $nota->cude);
            $nota->update([
                'estado' => 'validada',
                'xml_application_response' => $xmlResponse,
                'fecha_validacion_dian' => now(),
                'mensaje_dian' => 'Nota crédito procesada correctamente.',
            ]);

            // Cambiar estado de la factura a anulada
            $this->cambiarEstado($factura, EstadoFacturaEnum::Anulada, 'usuario', "Nota crédito de anulación: {$nota->numero_completo}");
            $factura->save();

            FacturaAnulada::dispatch($factura);

            return $nota;
        });
    }

    /**
     * Registra un evento del receptor (030, 031, 032, 033).
     */
    public function registrarEventoReceptor(FeFactura $factura, EventoReceptorEnum $tipoEvento, string $observaciones = ''): FeEventoReceptor
    {
        if (! $factura->esValidada()) {
            throw new RuntimeException('Solo se pueden registrar eventos del receptor en facturas validadas.');
        }

        // Validar que no exista ya este tipo de evento
        if ($factura->eventosReceptor()->where('tipo_evento', $tipoEvento->value)->exists()) {
            throw new RuntimeException("El evento {$tipoEvento->label()} ya fue registrado para esta factura.");
        }

        // Validar secuencia: 032 requiere 030 previo, 033/031 requieren 032
        if ($tipoEvento === EventoReceptorEnum::ReciboBien) {
            if (! $factura->eventosReceptor()->where('tipo_evento', EventoReceptorEnum::AcuseRecibo->value)->exists()) {
                throw new RuntimeException('Debe registrar primero el Acuse de Recibo (030) antes del Recibo del Bien (032).');
            }
        }

        if (in_array($tipoEvento, [EventoReceptorEnum::AceptacionExpresa, EventoReceptorEnum::Reclamo])) {
            if (! $factura->eventosReceptor()->where('tipo_evento', EventoReceptorEnum::ReciboBien->value)->exists()) {
                throw new RuntimeException('Debe registrar primero el Recibo del Bien (032).');
            }
        }

        // Reclamo: verificar plazo de 3 días hábiles (aproximado: 3 días naturales × 1.5)
        if ($tipoEvento === EventoReceptorEnum::Reclamo) {
            $diasTranscurridos = $factura->fecha_emision->diffInDays(now());
            if ($diasTranscurridos > 5) {
                throw new RuntimeException('El plazo para presentar el Reclamo (031) es de 3 días hábiles a partir de la emisión. Este plazo ya venció.');
            }
        }

        // Calcular CUDE del evento (hash simple simulado)
        $cudeEvento = hash('sha384', implode('', [
            $tipoEvento->value,
            $factura->cufe,
            now()->format('Y-m-d H:i:s'),
            Str::uuid(),
        ]));

        try {
            return $factura->eventosReceptor()->create([
                'tipo_evento' => $tipoEvento->value,
                'cude_evento' => $cudeEvento,
                'fecha_evento' => now(),
                'observaciones' => $observaciones,
                'estado' => 'registrado',
                'user_id' => $factura->user_id,
            ]);
        } catch (UniqueConstraintViolationException) {
            throw new RuntimeException("El evento {$tipoEvento->label()} ya fue registrado para esta factura.");
        }
    }

    private function cambiarEstado(FeFactura $factura, EstadoFacturaEnum $nuevoEstado, string $origen, string $descripcion): void
    {
        $estadoAnterior = $factura->estado?->value;
        $factura->estado = $nuevoEstado;

        $factura->eventos()->create([
            'estado_anterior' => $estadoAnterior,
            'estado_nuevo' => $nuevoEstado->value,
            'origen' => $origen,
            'descripcion' => $descripcion,
            'user_id' => $factura->user_id,
            'created_at' => now(),
        ]);
    }
}
