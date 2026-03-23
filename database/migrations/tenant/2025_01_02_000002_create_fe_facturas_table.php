<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fe_facturas', function (Blueprint $table) {
            $table->id();

            $table->foreignId('resolucion_id')->constrained('fe_resoluciones');
            $table->unsignedInteger('numero');
            $table->string('numero_completo', 20);
            $table->string('cufe', 96)->nullable()->unique();

            $table->string('tipo_operacion', 10)->default('10');

            $table->date('fecha_emision');
            $table->time('hora_emision');

            $table->string('estado', 20)->default('borrador');

            // Datos del emisor (se copian de company_config al emitir)
            $table->string('nit_emisor', 20);
            $table->unsignedTinyInteger('dv_emisor');
            $table->string('razon_social_emisor', 255);
            $table->string('regimen_fiscal_emisor', 2)->default('48');

            // Datos del adquirente
            $table->string('tipo_doc_adquirente', 2)->default('31');
            $table->string('num_doc_adquirente', 20);
            $table->string('nombre_adquirente', 255);
            $table->string('email_adquirente', 255)->nullable();
            $table->string('telefono_adquirente', 20)->nullable();
            $table->string('direccion_adquirente', 255)->nullable();
            $table->string('municipio_adquirente', 5)->nullable();
            // cliente_id referencia thirds (clientes)
            $table->unsignedBigInteger('cliente_id')->nullable();
            $table->foreign('cliente_id')->references('id')->on('thirds')->nullOnDelete();

            // Totales
            $table->decimal('subtotal', 18, 2);
            $table->decimal('total_descuentos', 18, 2)->default(0);
            $table->decimal('base_iva', 18, 2)->default(0);
            $table->decimal('valor_iva', 18, 2)->default(0);
            $table->decimal('base_ica', 18, 2)->default(0);
            $table->decimal('valor_ica', 18, 2)->default(0);
            $table->decimal('base_inc', 18, 2)->default(0);
            $table->decimal('valor_inc', 18, 2)->default(0);
            $table->decimal('total_retenciones', 18, 2)->default(0);
            $table->decimal('total', 18, 2);

            // Condiciones de pago
            $table->string('medio_pago', 2)->default('10');
            $table->string('forma_pago', 2)->default('1');
            $table->date('fecha_vencimiento_pago')->nullable();

            // XML generado
            $table->longText('xml_factura')->nullable();
            $table->longText('xml_application_response')->nullable();

            // Respuesta DIAN simulada
            $table->timestamp('fecha_validacion_dian')->nullable();
            $table->string('codigo_respuesta_dian', 10)->nullable();
            $table->text('mensaje_dian')->nullable();

            $table->text('qr_data')->nullable();
            $table->text('notas')->nullable();

            // user_id sin FK (no hay tabla users en schema tenant)
            $table->unsignedBigInteger('user_id')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['resolucion_id', 'numero']);
            $table->index(['estado', 'fecha_emision']);
            $table->index('nit_emisor');
            $table->index('num_doc_adquirente');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fe_facturas');
    }
};
