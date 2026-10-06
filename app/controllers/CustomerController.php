<?php
class CustomerController extends Controller
{
    public function dashboard(): void
    {
        $customerModel = new CustomerModel();
        $invoiceModel = new InvoiceModel();

        $customer = $customerModel->findByUserId(Auth::id());
        $invoices = $customer ? $invoiceModel->listWithDetails(['customer_id' => $customer['id']]) : [];

        $this->view('customer/dashboard', [
            'customer' => $customer,
            'invoices' => $invoices,
            'pageTitle' => 'My Dashboard',
        ]);
    }
}
