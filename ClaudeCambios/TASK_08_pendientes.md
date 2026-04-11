# TASK 08 — Estado final
> Actualizado: 2026-04-11

## ✅ TODO COMPLETADO

### Fase 1-3: Core bancario
- [x] 5 migraciones tenant: bank_accounts, bank_transactions, bank_statements, bank_documents, bank_checks
- [x] 5 modelos Eloquent: BankAccount, BankTransaction, BankStatement, BankDocument, BankCheck
- [x] BankService: tarifas, GMF, generarNumeroCuenta, crearCuentaPrincipal, procesarFinDeMes
- [x] AutoMigrateTenant: asigna banco aleatorio al crear tenant nuevo
- [x] Rutas en todos los grupos (student, teacher.demo, teacher.auditoria, coordinator.auditoria, student.referencia)
- [x] Navegación sidebar: entrada "Banco" con ícono credit-card

### Fase 4: Integración Negocios / Compras
- [x] Migración central: buyer_bank_account_id + seller_bank en intercompany_invoices
- [x] IntercompanyService.accept(): registra BankTransaction en comprador y vendedor
- [x] Negocios/Index.php: buyer_bank_account_id + validación saldo
- [x] Migración tenant: bank_account_id en payments
- [x] AccountingService.generatePaymentEntry(): usa 1110 + GMF si hay bank_account_id
- [x] PurchaseService.applyPayment(): retorna [$payment, $entry]
- [x] Compras/Index.php: payment_bank_account_id + validación saldo + BankTransaction

### Fase 5: UI bancaria completa
- [x] Livewire Banco/Index.php: cuentas, movimientos, documentos, chequera, transferencias, cheque, sobregiro, alertas, fin de mes
- [x] Vista banco/index.blade.php: alertas, tab Transferencias, modales Transferencia / Emitir cheque / Cupo Ágil
- [x] negocios/index.blade.php → selector "Forma de pago"
- [x] compras/index.blade.php → selector "Pagar desde" con estimado GMF

### Fase 6: PDFs documentos bancarios
- [x] BancoPdfController: genera extracto/certificado/referencia/paz_y_salvo con dompdf
- [x] 4 vistas PDF: pdf/banco/{extracto,certificado,referencia,paz_y_salvo}.blade.php
- [x] Ruta `/banco/documento/pdf?id=X` en los 5 grupos de rutas
- [x] Link "PDF" en historial de documentos de la vista banco

### Fase 7: Bloqueo sobregiro 2 períodos
- [x] Migración: sobregiro_periodos en bank_accounts
- [x] BankService::procesarFinDeMes(): incrementa contador, bloquea en período 2, resetea si paga

### Fase 8: Panel del profesor — actividad bancaria
- [x] Teacher/Dashboard.php: cross-tenant query con $tenant->run() para bank_accounts
- [x] teacher/dashboard.blade.php: columna "Saldo banco" con dots de color + estado bloqueada/sobregiro

### Fase 9: Integración bidireccional con Conciliación
- [x] Migración tenant: bank_account_id en bank_reconciliations
- [x] BankReconciliation: bank_account_id en fillable + relación bankAccount()
- [x] BankReconciliationService::create(): carga bank_transactions no conciliadas como ítems extracto
- [x] BankReconciliationService::addBankItem(): crea BankTransaction + actualiza saldo si hay banco vinculado
- [x] Conciliacion/Index.php: propiedad rc_bank_account_id, bankAccountsModulo en render()
- [x] Vista conciliacion: selector cuenta banco módulo en formulario nueva conciliación

---

## Comandos para migrar

```bash
# Migraciones tenant (todas las nuevas de bank_*)
php artisan tenants:run migrate --option="path=database/migrations/tenant"

# Migraciones centrales
php artisan migrate
```
