<?php

namespace App\Booking\Services;

use App\Properties\Models\Property;
use Carbon\Carbon;

class BookingPriceCalculator
{
    public function calculateTotal(
        Property $property,
        string $startDate,
        string $endDate,
        int $adults = 1,
        int $children = 0,
        int $pets = 0,
    ): array {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        $nights = $start->diffInDays($end);

        $nightlyRate = $property->getPrice() ?? 0;
        $currency = $property->getCurrency() ?? 'USD';

        $subtotal = $nightlyRate * $nights;
        $cleaningFee = $property->getCleaningFee() ?? 0;
        $serviceFee = round($subtotal * 0.05, 2);
        $damageDeposit = $property->getDamageDeposit() ?? 0;
        $totalAmount = $subtotal + $cleaningFee + $serviceFee + $damageDeposit;

        return [
            'nights' => $nights,
            'nightly_rate' => $nightlyRate,
            'currency' => $currency,
            'subtotal' => $subtotal,
            'cleaning_fee' => $cleaningFee,
            'service_fee' => $serviceFee,
            'damage_deposit' => $damageDeposit,
            'total_amount' => $totalAmount,
            'adults' => $adults,
            'children' => $children,
            'pets' => $pets,
        ];
    }
}
