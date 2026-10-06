<?php
/**
 * BillingEngine — computes tiered ("progressive") water pricing for a given
 * consumption volume, the way most municipal water utilities bill: the first
 * block of m3 is cheap, and price per m3 increases as usage climbs.
 *
 * Tiers are stored as JSON in settings.tier_pricing, e.g.:
 * [
 *   {"from":0,  "to":10,  "price":3.50},
 *   {"from":10, "to":30,  "price":6.00},
 *   {"from":30, "to":60,  "price":9.50},
 *   {"from":60, "to":null,"price":13.00}
 * ]
 */
class BillingEngine
{
    private SettingModel $settings;

    public function __construct()
    {
        $this->settings = new SettingModel();
    }

    /**
     * @return array{lines: array, subtotal: float, tax_rate: float, tax_amount: float, total: float}
     */
    public function calculate(float $consumption): array
    {
        $tiers = $this->settings->tierPricing();
        $taxRate = (float) $this->settings->get('tax_rate', '7.00');

        $remaining = $consumption;
        $lines = [];
        $subtotal = 0.0;

        foreach ($tiers as $tier) {
            if ($remaining <= 0) {
                break;
            }
            $from = (float) $tier['from'];
            $to   = $tier['to'] !== null ? (float) $tier['to'] : null;
            $tierCapacity = $to !== null ? ($to - $from) : $remaining;

            $volumeInTier = min($remaining, $tierCapacity);
            if ($volumeInTier <= 0) {
                continue;
            }

            $lineTotal = round($volumeInTier * (float) $tier['price'], 2);
            $subtotal += $lineTotal;

            $label = $to !== null
                ? "Tier ({$from}-{$to} m3)"
                : "Tier ({$from}+ m3)";

            $lines[] = [
                'tier_label' => $label,
                'tier_from'  => $from,
                'tier_to'    => $to,
                'volume_m3'  => round($volumeInTier, 2),
                'unit_price' => (float) $tier['price'],
                'line_total' => $lineTotal,
            ];

            $remaining -= $volumeInTier;
        }

        $subtotal   = round($subtotal, 2);
        $taxAmount  = round($subtotal * ($taxRate / 100), 2);
        $total      = round($subtotal + $taxAmount, 2);

        return [
            'lines'      => $lines,
            'subtotal'   => $subtotal,
            'tax_rate'   => $taxRate,
            'tax_amount' => $taxAmount,
            'total'      => $total,
        ];
    }
}
