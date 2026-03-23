<?php

namespace App\Services\FacturacionElectronica;

use App\Models\Tenant\FeFactura;
use App\Models\Tenant\FeNotaCredito;

/**
 * Genera documentos XML según el estándar UBL 2.1 requerido por la DIAN.
 * Nota: En el simulador NO se incluye firma digital XAdES-EPES.
 */
class XmlUblService
{
    public function generarFactura(FeFactura $factura): string
    {
        $lineas = $this->generarLineas($factura);

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<Invoice xmlns="urn:oasis:names:specification:ubl:schema:xsd:Invoice-2"'."\n";
        $xml .= '         xmlns:cac="urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2"'."\n";
        $xml .= '         xmlns:cbc="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2">'."\n";
        $xml .= "  <cbc:UBLVersionID>UBL 2.1</cbc:UBLVersionID>\n";
        $xml .= "  <cbc:CustomizationID>{$factura->tipo_operacion}</cbc:CustomizationID>\n";
        $xml .= "  <cbc:ProfileID>DIAN 2.1</cbc:ProfileID>\n";
        $xml .= "  <cbc:ProfileExecutionID>{$factura->resolucion->ambiente}</cbc:ProfileExecutionID>\n";
        $xml .= '  <cbc:ID>'.e($factura->numero_completo)."</cbc:ID>\n";
        $xml .= "  <cbc:UUID schemeName=\"CUFE-SHA384\">{$factura->cufe}</cbc:UUID>\n";
        $xml .= "  <cbc:IssueDate>{$factura->fecha_emision->format('Y-m-d')}</cbc:IssueDate>\n";
        $xml .= "  <cbc:IssueTime>{$factura->hora_emision->format('H:i:s')}</cbc:IssueTime>\n";
        $xml .= "  <cbc:InvoiceTypeCode>01</cbc:InvoiceTypeCode>\n";
        $xml .= "  <cbc:DocumentCurrencyCode>COP</cbc:DocumentCurrencyCode>\n";
        $xml .= '  <cbc:LineCountNumeric>'.$factura->detalles->count()."</cbc:LineCountNumeric>\n";

        // Referencia resolución
        $res = $factura->resolucion;
        $xml .= "  <cac:InvoicePeriod>\n";
        $xml .= "    <cbc:StartDate>{$res->fecha_desde->format('Y-m-d')}</cbc:StartDate>\n";
        $xml .= "    <cbc:EndDate>{$res->fecha_hasta->format('Y-m-d')}</cbc:EndDate>\n";
        $xml .= '    <cbc:Description>Resolución DIAN: '.e($res->numero_resolucion)."</cbc:Description>\n";
        $xml .= "  </cac:InvoicePeriod>\n";

        // Emisor
        $xml .= "  <cac:AccountingSupplierParty>\n";
        $xml .= "    <cac:Party>\n";
        $xml .= "      <cac:PartyTaxScheme>\n";
        $xml .= "        <cbc:CompanyID schemeID=\"{$factura->dv_emisor}\" schemeName=\"31\">{$factura->nit_emisor}</cbc:CompanyID>\n";
        $xml .= "        <cac:TaxScheme><cbc:ID>ZZ</cbc:ID></cac:TaxScheme>\n";
        $xml .= "      </cac:PartyTaxScheme>\n";
        $xml .= "      <cac:PartyLegalEntity>\n";
        $xml .= '        <cbc:RegistrationName>'.e($factura->razon_social_emisor)."</cbc:RegistrationName>\n";
        $xml .= "      </cac:PartyLegalEntity>\n";
        $xml .= "    </cac:Party>\n";
        $xml .= "  </cac:AccountingSupplierParty>\n";

        // Adquirente
        $xml .= "  <cac:AccountingCustomerParty>\n";
        $xml .= "    <cac:Party>\n";
        $xml .= "      <cac:PartyTaxScheme>\n";
        $xml .= "        <cbc:CompanyID schemeName=\"{$factura->tipo_doc_adquirente}\">".e($factura->num_doc_adquirente)."</cbc:CompanyID>\n";
        $xml .= "        <cac:TaxScheme><cbc:ID>ZZ</cbc:ID></cac:TaxScheme>\n";
        $xml .= "      </cac:PartyTaxScheme>\n";
        $xml .= "      <cac:PartyLegalEntity>\n";
        $xml .= '        <cbc:RegistrationName>'.e($factura->nombre_adquirente)."</cbc:RegistrationName>\n";
        $xml .= "      </cac:PartyLegalEntity>\n";
        if ($factura->email_adquirente) {
            $xml .= '      <cac:Contact><cbc:ElectronicMail>'.e($factura->email_adquirente)."</cbc:ElectronicMail></cac:Contact>\n";
        }
        $xml .= "    </cac:Party>\n";
        $xml .= "  </cac:AccountingCustomerParty>\n";

        // Pago
        $xml .= "  <cac:PaymentMeans>\n";
        $xml .= "    <cbc:ID>{$factura->medio_pago}</cbc:ID>\n";
        $xml .= "    <cbc:PaymentMeansCode>{$factura->forma_pago}</cbc:PaymentMeansCode>\n";
        $xml .= "  </cac:PaymentMeans>\n";

        // Impuestos totales
        if ((float) $factura->valor_iva > 0) {
            $xml .= "  <cac:TaxTotal>\n";
            $xml .= '    <cbc:TaxAmount currencyID="COP">'.number_format((float) $factura->valor_iva, 2, '.', '')."</cbc:TaxAmount>\n";
            $xml .= "    <cac:TaxSubtotal>\n";
            $xml .= '      <cbc:TaxableAmount currencyID="COP">'.number_format((float) $factura->base_iva, 2, '.', '')."</cbc:TaxableAmount>\n";
            $xml .= '      <cbc:TaxAmount currencyID="COP">'.number_format((float) $factura->valor_iva, 2, '.', '')."</cbc:TaxAmount>\n";
            $xml .= "      <cac:TaxCategory><cac:TaxScheme><cbc:ID>01</cbc:ID><cbc:Name>IVA</cbc:Name></cac:TaxScheme></cac:TaxCategory>\n";
            $xml .= "    </cac:TaxSubtotal>\n";
            $xml .= "  </cac:TaxTotal>\n";
        }

        // Totales monetarios
        $xml .= "  <cac:LegalMonetaryTotal>\n";
        $xml .= '    <cbc:LineExtensionAmount currencyID="COP">'.number_format((float) $factura->subtotal, 2, '.', '')."</cbc:LineExtensionAmount>\n";
        $xml .= '    <cbc:TaxExclusiveAmount currencyID="COP">'.number_format((float) $factura->base_iva, 2, '.', '')."</cbc:TaxExclusiveAmount>\n";
        $xml .= '    <cbc:TaxInclusiveAmount currencyID="COP">'.number_format((float) $factura->total, 2, '.', '')."</cbc:TaxInclusiveAmount>\n";
        $xml .= '    <cbc:AllowanceTotalAmount currencyID="COP">'.number_format((float) $factura->total_descuentos, 2, '.', '')."</cbc:AllowanceTotalAmount>\n";
        $xml .= '    <cbc:PayableAmount currencyID="COP">'.number_format((float) $factura->total, 2, '.', '')."</cbc:PayableAmount>\n";
        $xml .= "  </cac:LegalMonetaryTotal>\n";

        // Líneas
        $xml .= $lineas;

        $xml .= "</Invoice>\n";

        return $xml;
    }

    private function generarLineas(FeFactura $factura): string
    {
        $xml = '';
        foreach ($factura->detalles as $detalle) {
            $xml .= "  <cac:InvoiceLine>\n";
            $xml .= "    <cbc:ID>{$detalle->orden}</cbc:ID>\n";
            $xml .= '    <cbc:InvoicedQuantity unitCode="'.e($detalle->unidad_medida).'">'.number_format((float) $detalle->cantidad, 4, '.', '')."</cbc:InvoicedQuantity>\n";
            $xml .= '    <cbc:LineExtensionAmount currencyID="COP">'.number_format((float) $detalle->subtotal_linea, 2, '.', '')."</cbc:LineExtensionAmount>\n";
            $xml .= "    <cac:TaxTotal>\n";
            $xml .= '      <cbc:TaxAmount currencyID="COP">'.number_format((float) $detalle->valor_iva, 2, '.', '')."</cbc:TaxAmount>\n";
            $xml .= "    </cac:TaxTotal>\n";
            $xml .= "    <cac:Item>\n";
            $xml .= '      <cbc:Description>'.e($detalle->descripcion)."</cbc:Description>\n";
            if ($detalle->codigo_producto) {
                $xml .= '      <cac:SellersItemIdentification><cbc:ID>'.e($detalle->codigo_producto)."</cbc:ID></cac:SellersItemIdentification>\n";
            }
            $xml .= "    </cac:Item>\n";
            $xml .= "    <cac:Price>\n";
            $xml .= '      <cbc:PriceAmount currencyID="COP">'.number_format((float) $detalle->precio_unitario, 4, '.', '')."</cbc:PriceAmount>\n";
            $xml .= "    </cac:Price>\n";
            $xml .= "  </cac:InvoiceLine>\n";
        }

        return $xml;
    }

    public function generarNota(FeNotaCredito $nota): string
    {
        $factura = $nota->facturaOrigen;

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<CreditNote xmlns="urn:oasis:names:specification:ubl:schema:xsd:CreditNote-2"'."\n";
        $xml .= '            xmlns:cac="urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2"'."\n";
        $xml .= '            xmlns:cbc="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2">'."\n";
        $xml .= "  <cbc:UBLVersionID>UBL 2.1</cbc:UBLVersionID>\n";
        $xml .= "  <cbc:ProfileID>DIAN 2.1</cbc:ProfileID>\n";
        $xml .= '  <cbc:ID>'.e($nota->numero_completo)."</cbc:ID>\n";
        $xml .= "  <cbc:UUID schemeName=\"CUDE-SHA384\">{$nota->cude}</cbc:UUID>\n";
        $xml .= "  <cbc:IssueDate>{$nota->fecha_emision->format('Y-m-d')}</cbc:IssueDate>\n";
        $xml .= "  <cbc:IssueTime>{$nota->hora_emision->format('H:i:s')}</cbc:IssueTime>\n";
        $xml .= "  <cbc:DocumentCurrencyCode>COP</cbc:DocumentCurrencyCode>\n";
        $xml .= "  <cac:DiscrepancyResponse>\n";
        $xml .= '    <cbc:ReferenceID>'.e($factura->numero_completo)."</cbc:ReferenceID>\n";
        $xml .= "    <cbc:ResponseCode>{$nota->codigo_concepto}</cbc:ResponseCode>\n";
        $xml .= '    <cbc:Description>'.e($nota->descripcion_concepto)."</cbc:Description>\n";
        $xml .= "  </cac:DiscrepancyResponse>\n";
        $xml .= "  <cac:BillingReference>\n";
        $xml .= "    <cac:InvoiceDocumentReference>\n";
        $xml .= '      <cbc:ID>'.e($factura->numero_completo)."</cbc:ID>\n";
        $xml .= "      <cbc:UUID>{$factura->cufe}</cbc:UUID>\n";
        $xml .= "    </cac:InvoiceDocumentReference>\n";
        $xml .= "  </cac:BillingReference>\n";
        $xml .= '  <cbc:LineExtensionAmount currencyID="COP">'.number_format((float) $nota->subtotal, 2, '.', '')."</cbc:LineExtensionAmount>\n";
        $xml .= '  <cbc:PayableAmount currencyID="COP">'.number_format((float) $nota->total, 2, '.', '')."</cbc:PayableAmount>\n";
        $xml .= "</CreditNote>\n";

        return $xml;
    }

    public function generarApplicationResponse(bool $aceptada, string $codigo, string $mensaje, string $cufe): string
    {
        $estado = $aceptada ? 'DIAN Aceptado' : 'DIAN Rechazado';
        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= "<ApplicationResponse>\n";
        $xml .= '  <cbc:ID>'.uniqid('AR')."</cbc:ID>\n";
        $xml .= '  <cbc:IssueDate>'.now()->format('Y-m-d')."</cbc:IssueDate>\n";
        $xml .= '  <cbc:IssueTime>'.now()->format('H:i:s')."</cbc:IssueTime>\n";
        $xml .= "  <cac:DocumentResponse>\n";
        $xml .= "    <cac:Response>\n";
        $xml .= "      <cbc:ResponseCode>{$codigo}</cbc:ResponseCode>\n";
        $xml .= '      <cbc:Description>'.e($mensaje)."</cbc:Description>\n";
        $xml .= "    </cac:Response>\n";
        $xml .= "    <cac:DocumentReference>\n";
        $xml .= "      <cbc:UUID schemeName=\"CUFE-SHA384\">{$cufe}</cbc:UUID>\n";
        $xml .= "      <cbc:DocumentStatusCode>{$estado}</cbc:DocumentStatusCode>\n";
        $xml .= "    </cac:DocumentReference>\n";
        $xml .= "  </cac:DocumentResponse>\n";
        $xml .= "</ApplicationResponse>\n";

        return $xml;
    }
}
