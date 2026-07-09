<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\User;
use App\Models\Car;
use App\Models\Faq;
use App\Models\Review;
use App\Models\Coupon;
use App\Models\BlogPost;
use App\Models\PricingRule;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // 1. Seed default pricing rules
        if (PricingRule::count() === 0) {
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

        // 2. Seed default categories
        if (Category::count() === 0) {
            Category::insert([
                [
                    'name' => 'Economy',
                    'slug' => 'economy',
                    'description' => 'Affordable and fuel-efficient cars for everyday commuting.',
                    'icon' => 'CarIcon',
                    'image' => null,
                    'sort_order' => 1,
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'name' => 'Sedan',
                    'slug' => 'sedan',
                    'description' => 'Comfortable and stylish passenger cars with ample trunk space.',
                    'icon' => 'SedanIcon',
                    'image' => null,
                    'sort_order' => 2,
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'name' => 'SUV',
                    'slug' => 'suv',
                    'description' => 'Spacious and robust sports utility vehicles for family trips.',
                    'icon' => 'SuvIcon',
                    'image' => null,
                    'sort_order' => 3,
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'name' => 'Luxury',
                    'slug' => 'luxury',
                    'description' => 'Premium high-end vehicles for executive style and comfort.',
                    'icon' => 'LuxuryIcon',
                    'image' => null,
                    'sort_order' => 4,
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'name' => 'Electric',
                    'slug' => 'electric',
                    'description' => 'Eco-friendly fully electric vehicles with cutting-edge tech.',
                    'icon' => 'ElectricIcon',
                    'image' => null,
                    'sort_order' => 5,
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            ]);
        }

        // 3. Seed users
        if (User::count() === 0) {
            $admin = User::create([
                'name' => 'Apex Admin',
                'email' => 'admin@apexride.com',
                'email_verified_at' => $now,
                'password' => Hash::make('password'),
                'role' => 'admin',
                'balance' => 50000.00,
                'phone' => '8801700000000',
                'license_verified' => true,
            ]);

            $host = User::create([
                'name' => 'Nextora Host',
                'email' => 'host@apexride.com',
                'email_verified_at' => $now,
                'password' => Hash::make('password'),
                'role' => 'host',
                'balance' => 15000.00,
                'phone' => '8801700000001',
                'license_verified' => true,
            ]);

            $customer = User::create([
                'name' => 'John Doe',
                'email' => 'user@apexride.com',
                'email_verified_at' => $now,
                'password' => Hash::make('password'),
                'role' => 'user',
                'balance' => 2500.00,
                'phone' => '8801700000002',
                'license_verified' => true,
                'license_number' => 'DL-BD-98231',
                'license_expiry' => Carbon::now()->addYears(3),
                'license_country' => 'Bangladesh',
            ]);

            // 4. Seed cars (associated with the Host user)
            if (Car::count() === 0) {
                $cars = [
                    [
                        'name' => 'Toyota Aqua Hybrid',
                        'brand' => 'Toyota',
                        'category' => 'Economy',
                        'price' => 120.00,
                        'seats' => 5,
                        'transmission' => 'Automatic',
                        'fuel' => 'Hybrid',
                        'power' => '99 HP',
                        'speed' => '160 km/h',
                        'description' => 'Very fuel efficient hybrid hatchback, perfect for city rides and daily commuting around Dhaka.',
                        'features' => json_encode(['Bluetooth', 'Air Conditioner', 'Backup Camera', 'Keyless Entry']),
                        'image' => 'https://images.unsplash.com/photo-1549399542-7e3f8b79c341?auto=format&fit=crop&w=600&q=80',
                        'location' => 'Banani, Dhaka',
                        'latitude' => 23.7937,
                        'longitude' => 90.4066,
                        'rating' => 4.8,
                        'reviews_count' => 1,
                        'is_available' => true,
                        'year' => 2019,
                        'user_id' => $host->id,
                    ],
                    [
                        'name' => 'Hyundai Santa Fe SUV',
                        'brand' => 'Hyundai',
                        'category' => 'SUV',
                        'price' => 250.00,
                        'seats' => 7,
                        'transmission' => 'Automatic',
                        'fuel' => 'Octane',
                        'power' => '185 HP',
                        'speed' => '190 km/h',
                        'description' => 'Spacious 7-seater SUV with premium comfort, safety features, and a powerful engine. Great for family road trips.',
                        'features' => json_encode(['Sunroof', 'Leather Seats', 'Apple CarPlay', 'Cruise Control', 'Air Conditioner']),
                        'image' => 'https://images.unsplash.com/photo-1533473359331-0135ef1b58bf?auto=format&fit=crop&w=600&q=80',
                        'location' => 'Gulshan, Dhaka',
                        'latitude' => 23.7925,
                        'longitude' => 90.4162,
                        'rating' => 4.9,
                        'reviews_count' => 1,
                        'is_available' => true,
                        'year' => 2021,
                        'user_id' => $host->id,
                    ],
                    [
                        'name' => 'Tesla Model 3',
                        'brand' => 'Tesla',
                        'category' => 'Electric',
                        'price' => 400.00,
                        'seats' => 5,
                        'transmission' => 'Automatic',
                        'fuel' => 'Electric',
                        'power' => '283 HP',
                        'speed' => '225 km/h',
                        'description' => 'Fully electric sedan with cutting-edge tech, minimalist interior, autopilot options, and zero emissions.',
                        'features' => json_encode(['Autopilot', 'Giant Touchscreen', 'Heated Seats', 'Wireless Charging', 'Eco Mode']),
                        'image' => 'https://images.unsplash.com/photo-1619767886558-efdc259cde1a?auto=format&fit=crop&w=600&q=80',
                        'location' => 'Dhanmondi, Dhaka',
                        'latitude' => 23.7461,
                        'longitude' => 90.3742,
                        'rating' => 5.0,
                        'reviews_count' => 1,
                        'is_available' => true,
                        'year' => 2022,
                        'user_id' => $host->id,
                    ],
                    [
                        'name' => 'Honda Civic Sedan',
                        'brand' => 'Honda',
                        'category' => 'Sedan',
                        'price' => 150.00,
                        'seats' => 5,
                        'transmission' => 'Automatic',
                        'fuel' => 'Octane',
                        'power' => '158 HP',
                        'speed' => '200 km/h',
                        'description' => 'Sleek and sporty passenger sedan offering a smooth ride, excellent handling, and modern safety features.',
                        'features' => json_encode(['Lane Keep Assist', 'Backup Camera', 'Air Conditioner', 'Sport Mode']),
                        'image' => 'https://images.unsplash.com/photo-1533473359331-0135ef1b58bf?auto=format&fit=crop&w=600&q=80',
                        'location' => 'Uttara, Dhaka',
                        'latitude' => 23.8759,
                        'longitude' => 90.3795,
                        'rating' => 4.7,
                        'reviews_count' => 1,
                        'is_available' => true,
                        'year' => 2020,
                        'user_id' => $host->id,
                    ],
                ];

                foreach ($cars as $carData) {
                    $car = Car::create($carData);

                    // Seed a dummy review for each car
                    Review::create([
                        'user_id' => $customer->id,
                        'car_id' => $car->id,
                        'name' => $customer->name,
                        'avatar' => null,
                        'rating' => $car->rating,
                        'car_condition' => 5.0,
                        'driver_rating' => 5.0,
                        'value_rating' => 4.8,
                        'cleanliness' => 5.0,
                        'text' => 'The car was extremely clean and ran smoothly. Excellent communication with the host. Highly recommend!',
                        'is_verified' => true,
                        'helpful_count' => 2,
                        'source' => 'apexride',
                        'date' => $now->subDays(3),
                    ]);
                }
            }
        }

        // 5. Seed default FAQs
        if (Faq::count() === 0) {
            Faq::insert([
                [
                    'question' => 'How do I book a car on Apex Ride?',
                    'answer' => 'Simply register/login, search for cars in your preferred location, select your pickup and return dates, and proceed to booking with payment (Stripe/SSLCOMMERZ).',
                    'sort_order' => 1,
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'question' => 'What documents are required to rent?',
                    'answer' => 'You must upload a valid driving license (which will be verified by our admin team) and a valid national ID/passport before taking delivery of the car.',
                    'sort_order' => 2,
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'question' => 'Is fuel included in the rental price?',
                    'answer' => 'No, fuel cost is not included in the booking price. The car is handed over with a certain amount of fuel and should be returned with the same amount.',
                    'sort_order' => 3,
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'question' => 'What is the cancellation policy?',
                    'answer' => 'Free cancellation with a full refund is available up to 24 hours prior to the scheduled pickup time. Cancellations made within 24 hours may incur a fee.',
                    'sort_order' => 4,
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            ]);
        }

        // 6. Seed Coupons
        if (Coupon::count() === 0) {
            Coupon::insert([
                [
                    'code' => 'WELCOME10',
                    'type' => 'percentage',
                    'value' => 10.00,
                    'min_booking_amount' => 500.00,
                    'max_uses' => 100,
                    'used_count' => 0,
                    'starts_at' => $now->subDay(),
                    'expires_at' => $now->addMonths(6),
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'code' => 'APEX500',
                    'type' => 'fixed',
                    'value' => 500.00,
                    'min_booking_amount' => 2000.00,
                    'max_uses' => 50,
                    'used_count' => 0,
                    'starts_at' => $now->subDay(),
                    'expires_at' => $now->addMonths(3),
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            ]);
        }

        // 7. Seed Blog Posts
        if (BlogPost::count() === 0) {
            BlogPost::insert([
                [
                    'slug' => 'top-5-road-trips-bangladesh',
                    'title' => 'Top 5 Scenic Road Trips to Take in Bangladesh',
                    'excerpt' => 'Discover the most beautiful highways and destinations to explore with your rental car this weekend.',
                    'content' => 'Bangladesh offers incredibly scenic drives if you know where to look. From the marine drive of Cox’s Bazar to the winding tea garden roads of Sylhet and Sreemangal, taking a road trip is one of the best ways to explore the country’s diverse landscape. Always make sure to check your car’s fluid levels and tires before embarking on a long journey!',
                    'category' => 'Travel',
                    'date' => $now->subDays(10),
                    'read_time' => '5 mins',
                    'image' => 'https://images.unsplash.com/photo-1549399542-7e3f8b79c341?auto=format&fit=crop&w=600&q=80',
                    'is_published' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'slug' => 'rent-vs-own-car',
                    'title' => 'Renting vs. Owning a Car in Dhaka: Which is Better?',
                    'excerpt' => 'Analyzing the costs, convenience, and logistics of car rental versus buying a private car in Bangladesh.',
                    'content' => 'With rising vehicle prices, high registration fees, maintenance costs, and parking troubles in Dhaka, owning a car has become a major financial burden. On-demand car rental services like Apex Ride offer a highly flexible alternative. Rent only when you need it, choose different models for different occasions, and leave the maintenance and depreciation to the hosts!',
                    'category' => 'Tips',
                    'date' => $now->subDays(5),
                    'read_time' => '4 mins',
                    'image' => 'https://images.unsplash.com/photo-1533473359331-0135ef1b58bf?auto=format&fit=crop&w=600&q=80',
                    'is_published' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            ]);
        }
    }
}
