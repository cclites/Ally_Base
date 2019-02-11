Invoiceables are any item that can be assigned to an invoice.

Currently, only Eloquent models are supported by Invoiceables, 
so all invoiceables should not only implement InvoiceableInterface
but also extend InvoiceableModel.

There are some models that are invoiceables that aren't in the
Invoiceable directory (ex. App\Shift) since they are globally used
and not tied just to billing.  All invoiceables should be registered
in the BaseInvoiceGenerator class with their proper database mapping.