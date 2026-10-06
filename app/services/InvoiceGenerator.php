<?php
/**
 * InvoiceGenerator — turns a validated meter reading into an invoice,
 * applying tiered pricing via BillingEngine. Can be run:
 *   - automatically right after an agent's reading is validated
 *   - in bulk for a whole billing period (e.g. via a monthly cron job)
 */
class InvoiceGenerator
{
    private InvoiceModel $invoices;
    private SettingModel $settings;
    private BillingEngine $billing;
    private NotificationModel $notifications;

    public function __construct()
    {
        $this->invoices      = new InvoiceModel();
        $this->settings      = new SettingModel();
        $this->billing       = new BillingEngine();
        $this->notifications = new NotificationModel();
    }

    public function generateFromReading(array $reading, int $customerUserId): ?int
    {
        // Avoid duplicate invoices for the same customer/period
        $exists = $this->invoices->first([
            'customer_id' => $reading['customer_id'],
            'period_year' => $reading['period_year'],
            'period_month' => $reading['period_month'],
        ]);
        if ($exists) {
            return $exists['id'];
        }

        $consumption = (float) $reading['consumption'];
        $calc = $this->billing->calculate($consumption);

        $dueDays = (int) $this->settings->get('invoice_due_days', 15);
        $issueDate = date('Y-m-d');
        $dueDate   = date('Y-m-d', strtotime("+{$dueDays} days"));

        $invoiceNumber = $this->invoices->nextInvoiceNumber((int)$reading['period_year'], (int)$reading['period_month']);

        $invoiceId = $this->invoices->create([
            'invoice_number' => $invoiceNumber,
            'customer_id'    => $reading['customer_id'],
            'meter_id'       => $reading['meter_id'],
            'reading_id'     => $reading['id'],
            'consumption'    => $consumption,
            'subtotal'       => $calc['subtotal'],
            'tax_rate'       => $calc['tax_rate'],
            'tax_amount'     => $calc['tax_amount'],
            'total_amount'   => $calc['total'],
            'issue_date'     => $issueDate,
            'due_date'       => $dueDate,
            'status'         => 'unpaid',
            'period_month'   => $reading['period_month'],
            'period_year'    => $reading['period_year'],
        ]);

        foreach ($calc['lines'] as $line) {
            $this->invoices->execute(
                "INSERT INTO invoice_details (invoice_id, tier_label, tier_from, tier_to, volume_m3, unit_price, line_total)
                 VALUES (:invoice_id, :tier_label, :tier_from, :tier_to, :volume_m3, :unit_price, :line_total)",
                [
                    'invoice_id' => $invoiceId,
                    'tier_label' => $line['tier_label'],
                    'tier_from'  => $line['tier_from'],
                    'tier_to'    => $line['tier_to'],
                    'volume_m3'  => $line['volume_m3'],
                    'unit_price' => $line['unit_price'],
                    'line_total' => $line['line_total'],
                ]
            );
        }

        $this->notifications->notify(
            $customerUserId,
            'new_invoice',
            'New invoice available',
            "Your invoice {$invoiceNumber} of " . number_format($calc['total'], 2) . " MAD is ready.",
            '/customer/invoices/' . $invoiceId
        );

        return $invoiceId;
    }

    /** Mark unpaid invoices whose due_date has passed as 'late'. Run on a daily schedule. */
    public function markLateInvoices(): int
    {
        $stmt = $this->invoices->query(
            "SELECT id FROM invoices WHERE status = 'unpaid' AND due_date < CURDATE()"
        );
        foreach ($stmt as $row) {
            $this->invoices->update($row['id'], ['status' => 'late']);
        }
        return count($stmt);
    }
}
