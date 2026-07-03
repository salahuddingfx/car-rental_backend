<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Car;
use App\Models\CmsContent;
use App\Models\Faq;
use App\Models\Offer;
use App\Models\Review;
use App\Models\Timeline;
use App\Models\ProcessStep;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@apexride.com',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'balance' => 0,
        ]);

        // Test user
        User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => Hash::make('password'),
            'role' => 'user',
            'balance' => 5000,
        ]);

        // Host
        User::create([
            'name' => 'Host User',
            'email' => 'host@example.com',
            'password' => Hash::make('password'),
            'role' => 'host',
            'balance' => 15000,
        ]);

        // Cars
        $cars = [
            ['name' => 'Toyota Camry', 'brand' => 'Toyota', 'category' => 'Sedan', 'price' => 1800, 'seats' => 5, 'transmission' => 'Automatic', 'fuel' => 'Petrol', 'power' => '203 HP', 'speed' => '240 km/h', 'location' => 'Banani, Dhaka', 'image' => 'https://images.unsplash.com/photo-1621007947382-bb3c3994e3fb?w=800', 'rating' => 4.8, 'reviews_count' => 120, 'features' => ['Air Conditioning', 'Bluetooth', 'Cruise Control', 'Backup Camera']],
            ['name' => 'Honda CR-V', 'brand' => 'Honda', 'category' => 'SUV', 'price' => 2200, 'seats' => 5, 'transmission' => 'Automatic', 'fuel' => 'Petrol', 'power' => '190 HP', 'speed' => '220 km/h', 'location' => 'Gulshan, Dhaka', 'image' => 'https://images.unsplash.com/photo-1568844293986-8d0400f4745b?w=800', 'rating' => 4.7, 'reviews_count' => 85, 'features' => ['All-Wheel Drive', 'Bluetooth', 'Sunroof', 'Navigation System']],
            ['name' => 'BMW 5 Series', 'brand' => 'BMW', 'category' => 'Sedan', 'price' => 4500, 'seats' => 5, 'transmission' => 'Automatic', 'fuel' => 'Diesel', 'power' => '265 HP', 'speed' => '280 km/h', 'location' => 'Dhanmondi, Dhaka', 'image' => 'https://images.unsplash.com/photo-1555215695-3004980ad54e?w=800', 'rating' => 4.9, 'reviews_count' => 65, 'features' => ['Leather Seats', 'Panoramic Sunroof', 'Harman Kardon', 'Heated Seats']],
            ['name' => 'Mercedes-Benz GLC', 'brand' => 'Mercedes-Benz', 'category' => 'SUV', 'price' => 5000, 'seats' => 5, 'transmission' => 'Automatic', 'fuel' => 'Diesel', 'power' => '255 HP', 'speed' => '260 km/h', 'location' => 'Motijheel, Dhaka', 'image' => 'https://images.unsplash.com/photo-1618843479313-40f8afb4b4d8?w=800', 'rating' => 4.9, 'reviews_count' => 45, 'features' => ['MBUX System', '360 Camera', 'Ambient Lighting', 'Wireless Charging']],
            ['name' => 'Toyota HiAce', 'brand' => 'Toyota', 'category' => 'Van', 'price' => 3500, 'seats' => 12, 'transmission' => 'Manual', 'fuel' => 'Diesel', 'power' => '136 HP', 'speed' => '180 km/h', 'location' => 'Uttara, Dhaka', 'image' => 'https://images.unsplash.com/photo-1570125909232-eb263c188f7e?w=800', 'rating' => 4.5, 'reviews_count' => 30, 'features' => ['Spacious Interior', 'Air Conditioning', 'USB Charging', 'Luggage Rack']],
            ['name' => 'Hyundai i20', 'brand' => 'Hyundai', 'category' => 'Hatchback', 'price' => 1200, 'seats' => 5, 'transmission' => 'Manual', 'fuel' => 'Petrol', 'power' => '83 HP', 'speed' => '200 km/h', 'location' => 'Mirpur, Dhaka', 'image' => 'https://images.unsplash.com/photo-1609521263047-f8f205293f24?w=800', 'rating' => 4.3, 'reviews_count' => 95, 'features' => ['Touchscreen', 'Bluetooth', 'Rear Camera', 'Keyless Entry']],
            ['name' => 'Nissan Patrol', 'brand' => 'Nissan', 'category' => 'SUV', 'price' => 6000, 'seats' => 7, 'transmission' => 'Automatic', 'fuel' => 'Petrol', 'power' => '400 HP', 'speed' => '250 km/h', 'location' => 'Baridhara, Dhaka', 'image' => 'https://images.unsplash.com/photo-1606664515524-ed2f786a0bd6?w=800', 'rating' => 4.8, 'reviews_count' => 40, 'features' => ['4x4', 'Leather Seats', 'Bose Audio', 'Climate Control']],
            ['name' => 'Honda Civic', 'brand' => 'Honda', 'category' => 'Sedan', 'price' => 1600, 'seats' => 5, 'transmission' => 'Automatic', 'fuel' => 'Petrol', 'power' => '158 HP', 'speed' => '230 km/h', 'location' => 'Mohammadpur, Dhaka', 'image' => 'https://images.unsplash.com/photo-1603386329225-868f9b1ee6c9?w=800', 'rating' => 4.6, 'reviews_count' => 110, 'features' => ['Honda Sensing', 'Sunroof', 'LED Headlights', 'Apple CarPlay']],
        ];

        foreach ($cars as $car) {
            Car::create([...$car, 'user_id' => $admin->id]);
        }

        // CMS Content
        $cmsData = [
            ['key' => 'company_name', 'group' => 'hero', 'value' => 'Apex Ride'],
            ['key' => 'hero_title', 'group' => 'hero', 'value' => 'Premium Car Rental in Bangladesh'],
            ['key' => 'hero_subtitle', 'group' => 'hero', 'value' => 'Experience luxury and comfort with our premium fleet of vehicles. Trusted by thousands across Bangladesh.'],
            ['key' => 'phone', 'group' => 'contact', 'value' => '+880 1700-000000'],
            ['key' => 'email', 'group' => 'contact', 'value' => 'info@apexride.com'],
            ['key' => 'address', 'group' => 'contact', 'value' => 'Banani, Dhaka 1213, Bangladesh'],
            ['key' => 'about_title', 'group' => 'about', 'value' => 'About Apex Ride'],
            ['key' => 'about_description', 'group' => 'about', 'value' => 'Apex Ride is Bangladesh\'s leading premium car rental platform. Founded in 2020, we have served over 10,000 satisfied customers with our fleet of 50+ luxury and economy vehicles. Our mission is to provide safe, reliable, and affordable transportation solutions across Bangladesh.'],
            ['key' => 'total_customers', 'group' => 'stats', 'value' => '10,000+'],
            ['key' => 'total_fleet', 'group' => 'stats', 'value' => '50+'],
            ['key' => 'total_years', 'group' => 'stats', 'value' => '5+'],
            ['key' => 'total_support', 'group' => 'stats', 'value' => '24/7'],
            ['key' => 'why_title', 'group' => 'why', 'value' => 'Why Choose Apex Ride?'],
            ['key' => 'why_subtitle', 'group' => 'why', 'value' => 'We deliver excellence in every journey'],
            ['key' => 'process_title', 'group' => 'process', 'value' => 'How It Works'],
            ['key' => 'process_subtitle', 'group' => 'process', 'value' => 'Rent your dream car in 5 simple steps'],
            ['key' => 'faq_title', 'group' => 'faq', 'value' => 'Frequently Asked Questions'],
            ['key' => 'faq_subtitle', 'group' => 'faq', 'value' => 'Find answers to common questions'],
        ];

        foreach ($cmsData as $item) {
            CmsContent::create($item);
        }

        // FAQs
        $faqs = [
            ['question' => 'What documents are required to rent a car?', 'answer' => 'You need a valid driving license, national ID card, and a valid payment method. For international tourists, an international driving permit is accepted.', 'sort_order' => 1],
            ['question' => 'Is there a minimum age requirement?', 'answer' => 'Yes, drivers must be at least 21 years old and have held their license for at least 1 year.', 'sort_order' => 2],
            ['question' => 'Can I rent a car with a driver?', 'answer' => 'Yes, we offer chauffeur-driven rental options for most vehicles. Simply select the "With Driver" option during booking.', 'sort_order' => 3],
            ['question' => 'What is the cancellation policy?', 'answer' => 'Free cancellation up to 24 hours before pickup. Cancellations within 24 hours may incur a 50% charge.', 'sort_order' => 4],
            ['question' => 'Do you offer airport pickup?', 'answer' => 'Yes, we offer airport pickup and drop-off services at Hazrat Shahjalal International Airport for an additional fee.', 'sort_order' => 5],
            ['question' => 'Are the cars insured?', 'answer' => 'All our vehicles come with comprehensive insurance coverage. Basic insurance is included in the rental price.', 'sort_order' => 6],
        ];
        foreach ($faqs as $faq) { Faq::create($faq); }

        // Offers
        Offer::create(['title' => 'Weekend Special', 'description' => 'Get 20% off on all SUVs this weekend. Book now and enjoy the ride!', 'cta_text' => 'Book Now', 'active' => true]);
        Offer::create(['title' => 'First Ride Free', 'description' => 'New users get their first ride free up to BDT 2,000. Sign up today!', 'cta_text' => 'Sign Up', 'active' => true]);

        // Reviews
        $reviews = [
            ['name' => 'Rahman Ahmed', 'rating' => 5, 'text' => 'Exceptional service! The Toyota Camry was in perfect condition and the booking process was seamless. Will definitely use Apex Ride again.', 'source' => 'google', 'date' => '2026-06-15'],
            ['name' => 'Fatima Khan', 'rating' => 5, 'text' => 'Best car rental experience in Dhaka. The BMW 5 Series was immaculate and the customer support was outstanding.', 'source' => 'facebook', 'date' => '2026-06-10'],
            ['name' => 'David Chen', 'rating' => 4, 'text' => 'Great selection of vehicles and competitive prices. The Honda CR-V was perfect for our family trip to Cox\'s Bazar.', 'source' => 'tripadvisor', 'date' => '2026-05-28'],
            ['name' => 'Sarah Mitchell', 'rating' => 5, 'text' => 'Highly recommend Apex Ride! Professional service and well-maintained cars. The Mercedes-Benz GLC exceeded my expectations.', 'source' => 'google', 'date' => '2026-05-20'],
            ['name' => 'Imran Hassan', 'rating' => 4, 'text' => 'Reliable and affordable. Used their service for a business trip and was impressed with the punctuality and car quality.', 'source' => 'apexride', 'date' => '2026-05-15'],
            ['name' => 'Nadia Rahman', 'rating' => 5, 'text' => 'The Toyota HiAce was perfect for our group trip. Clean, comfortable, and the driver was very professional.', 'source' => 'facebook', 'date' => '2026-04-30'],
        ];
        foreach ($reviews as $review) { Review::create($review); }

        // Timelines
        $timelines = [
            ['year' => '2020', 'title' => 'Company Founded', 'description' => 'Apex Ride started with just 5 cars in Banani, Dhaka.', 'icon' => 'Rocket', 'type' => 'journey', 'sort_order' => 1],
            ['year' => '2021', 'title' => 'Expanded Fleet', 'description' => 'Grew our fleet to 20 vehicles across Dhaka.', 'icon' => 'Car', 'type' => 'journey', 'sort_order' => 2],
            ['year' => '2022', 'title' => 'Nationwide Service', 'description' => 'Expanded operations to Chittagong, Sylhet, and Cox\'s Bazar.', 'icon' => 'MapPin', 'type' => 'journey', 'sort_order' => 3],
            ['year' => '2023', 'title' => '10,000+ Customers', 'description' => 'Reached the milestone of 10,000 satisfied customers.', 'icon' => 'Users', 'type' => 'journey', 'sort_order' => 4],
            ['year' => '2024', 'title' => 'Electric Fleet', 'description' => 'Introduced electric vehicles to our premium fleet.', 'icon' => 'Zap', 'type' => 'journey', 'sort_order' => 5],
        ];
        foreach ($timelines as $t) { Timeline::create($t); }

        // Process Steps
        $steps = [
            ['step' => 1, 'title' => 'Browse & Choose', 'description' => 'Browse our extensive fleet and select the perfect vehicle for your needs.', 'icon' => 'Search'],
            ['step' => 2, 'title' => 'Select Dates', 'description' => 'Choose your pickup and return dates that suit your schedule.', 'icon' => 'Calendar'],
            ['step' => 3, 'title' => 'Verify & Book', 'description' => 'Complete verification and confirm your booking instantly.', 'icon' => 'CheckCircle'],
            ['step' => 4, 'title' => 'Pick Up Car', 'description' => 'Collect your vehicle from our designated pickup location.', 'icon' => 'MapPin'],
            ['step' => 5, 'title' => 'Enjoy & Return', 'description' => 'Drive worry-free and return the car when you\'re done.', 'icon' => 'Smile'],
        ];
        foreach ($steps as $s) { ProcessStep::create($s); }
    }
}
