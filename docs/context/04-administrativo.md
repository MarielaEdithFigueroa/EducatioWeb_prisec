# 04 · Dominio administrativo

Referencia funcional del área **Administrativa** (facturación y cobranzas), derivada del módulo `Administrativo` del legacy (proyecto `EducatioAdm.vbp`). Describe **qué hace** el dominio, no cómo se modela en la base.

> El legacy tiene varias variantes de este módulo (`Administrativo`, `AdministrativoConIva`, `AdministrativoV4`). `AdministrativoV4` es la más reciente y es la referencia principal. Las diferencias entre variantes (tratamiento de IVA, integraciones bancarias) se resuelven con el PO al implementar.

## Aranceles y planes de cuotas

- **Planes de cuotas** por alumno/nivel y **actualización de cuotas** (ajustes de importe).
- **Becas** que reducen o eximen el arancel.
- Generación de **cuenta corriente masiva** (emitir las cuotas del período a todos los alumnos).

## Cuenta corriente

- **Cta Cte de alumnos** y **de empresas**; también **unificada** y **adicional**.
- **Empresas** con **planes de empresas** (convenios/obras sociales/empleadores que cubren aranceles).
- Consulta de **saldo** y **vencimientos**.

## Facturación

- **Facturación** individual y **masiva**; facturación **manual**; **detalle** y **consulta** de facturas.
- **Factura electrónica**: emisión con **CAE** de AFIP (webservice). **Precomprobantes** e impresión.
- **Notas de crédito** y **notas de débito**.
- **Anulación** de facturas (incluida anulación masiva) y de comprobantes.

## Cobranzas

- **Cobro** de cuenta corriente y de comprobantes, con múltiples medios: efectivo, **cheque**, **transferencia/banco**, **pronto pago**, **controlador fiscal** (Hasar).
- **Recibos** y su impresión; anulación de recibos.
- **Débito automático**: generación de archivos de envío y procesamiento de **devoluciones**, con distintas integraciones (PagoMisCuentas, First Data, Banco Itaú, MercadoPago, etc.). Manejo de **stop debit**.

## Caja

- **Apertura**, **movimientos**, **ajustes** (individuales y masivos) y **cierre de caja** (con cierre del día anterior).
- **Ingresos varios** (con o sin cliente) y **egresos**.

## Cheques y valores

- **Chequeras**, **cheques emitidos**, **ingreso de cheques**, **cambio de cheque**, **historial**.
- **Boletas bancarias**, **depósitos**, **extracciones**, **registro de valores**, **gastos bancarios**.

## Egresos y compras

- **Egresos** (con IVA / por cuenta), **órdenes de compra**, **proveedores**.
- **Retenciones** (tabla de retenciones, retenciones por cuenta).

## Morosos

- Identificación de **morosos**, **acciones** de cobranza, **estadísticas** y **parámetros** de mora.

## Contabilidad

- **Asientos** (automáticos y manuales), **plan de cuentas**, **libro diario**, **libro mayor**.
- **Ejercicio fiscal**, **cuentas de IVA**, **responsables** contables.

---

> Qué entra realmente en el MVP administrativo (y en qué orden) es una decisión del PO. Este doc es la referencia de alcance del legacy, no un compromiso de scope.
