<?php

namespace App\Services;

class InvoiceCalculator
{
    public function calculate(array $items, string $discountType = 'percent', float $discountValue = 0): array
    {
        $lines = [];
        $itemsSubtotal = 0.0;

        foreach ($items as $item) {
            $quantity  = (float) ($item['quantity'] ?? 0);
            $unitPrice = (float) ($item['unit_price'] ?? 0);
            $taxRate   = (float) ($item['tax_rate'] ?? 0);

            $lineSubtotal = round($quantity * $unitPrice, 2);
            $itemsSubtotal += $lineSubtotal;

            $lines[] = [
                'description'   => (string) ($item['description'] ?? ''),
                'quantity'      => $quantity,
                'unit_price'    => $unitPrice,
                'tax_rate'      => $taxRate,
                'line_subtotal' => $lineSubtotal,
                'line_tax'      => 0.0,
            ];
        }

        $itemsSubtotal = round($itemsSubtotal, 2);
        $discountAmount = $this->resolveDiscount($itemsSubtotal, $discountType, $discountValue);
        $discountRatio = $itemsSubtotal > 0 ? $discountAmount / $itemsSubtotal : 0.0;

        $taxTotal = 0.0;
        foreach ($lines as $index => $line) {
            $taxable = round($line['line_subtotal'] * (1 - $discountRatio), 2);
            $lineTax = round($taxable * $line['tax_rate'] / 100, 2);
            $lines[$index]['line_tax'] = $lineTax;
            $taxTotal += $lineTax;
        }

        $taxTotal = round($taxTotal, 2);
        $grandTotal = round($itemsSubtotal - $discountAmount + $taxTotal, 2);

        return [
            'items'           => $lines,
            'items_subtotal'  => $itemsSubtotal,
            'discount_amount' => $discountAmount,
            'tax_total'       => $taxTotal,
            'grand_total'     => $grandTotal,
        ];
    }

    private function resolveDiscount(float $itemsSubtotal, string $discountType, float $discountValue): float
    {
        if ($discountValue <= 0 || $itemsSubtotal <= 0) {
            return 0.0;
        }

        if ($discountType === 'fixed') {
            return round(min($discountValue, $itemsSubtotal), 2);
        }

        $percent = min($discountValue, 100);

        return round($itemsSubtotal * $percent / 100, 2);
    }
}
