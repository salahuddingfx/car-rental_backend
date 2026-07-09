<?php

namespace Database\Seeders;

use App\Models\PricingRule;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Seed default pricing rules if none exist
        if (PricingRule::count() === 0) {
            $now = Carbon::now();

            PricingRule::insert([
                [
                    'name' => 'Peak Hours',
                    'type' => 'peak_hour',
                    'multiplier' => 1.250,
                    'start_time' => '18:00:00',
                    'end_time' => '22:00:00',
                    'days_of_week' => null,
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'name' => 'Weekend',
                    'type' => 'weekend',
                    'multiplier' => 1.500,
                    'start_time' => null,
                    'end_time' => null,
                    'days_of_week' => json_encode([5, 6]), // Friday, Saturday
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'name' => 'Holiday',
                    'type' => 'holiday',
                    'multiplier' => 2.000,
                    'start_time' => null,
                    'end_time' => null,
                    'days_of_week' => null,
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            ]);
        }
    }
}
