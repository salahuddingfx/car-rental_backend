<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PricingRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'type', 'multiplier', 'start_time', 'end_time', 'days_of_week', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'multiplier' => 'decimal:3',
            'days_of_week' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function applyMultiplier(float $basePrice, string $date): array
    {
        $carbon = Carbon::parse($date);
        $applied = false;

        // Check if rule is active
        if (!$this->is_active) {
            return ['price' => $basePrice, 'applied' => false, 'rule' => null];
        }

        // Check day of week
        if ($this->days_of_week && count($this->days_of_week) > 0) {
            if (!in_array($carbon->dayOfWeek, $this->days_of_week)) {
                return ['price' => $basePrice, 'applied' => false, 'rule' => null];
            }
        }

        // Check time range
        if ($this->start_time && $this->end_time) {
            $checkTime = $carbon->format('H:i:s');
            if ($checkTime < $this->start_time || $checkTime > $this->end_time) {
                return ['price' => $basePrice, 'applied' => false, 'rule' => null];
            }
        }

        // Apply multiplier
        $newPrice = $basePrice * $this->multiplier;

        return [
            'price' => $newPrice,
            'applied' => true,
            'rule' => [
                'name' => $this->name,
                'type' => $this->type,
                'multiplier' => $this->multiplier,
            ],
        ];
    }
}
