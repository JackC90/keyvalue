<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\KeyValueObject;

class KeyValueObjectSeeder extends Seeder
{
    public function run(): void
    {
        // Create some random records
        KeyValueObject::factory(10)->create();

        // Create some specific test records
        KeyValueObject::factory()->withKey('product')->withValue([
            'name' => 'Premium Widget',
            'description' => 'High quality premium widget for all your needs',
            'price' => 9900,
            'address' => [
                'street' => '123 Manufacturing St',
                'city' => 'Industrial City',
                'country' => 'Productland',
            ],
            'active' => true,
        ])->create();

        KeyValueObject::factory()->withKey('product')->withValue([
            'name' => 'Basic Gadget',
            'description' => 'Affordable gadget for everyday use',
            'price' => 2500,
            'address' => [
                'street' => '456 Warehouse Ave',
                'city' => 'Storage Town',
                'country' => 'Gadgetville',
            ],
            'active' => true,
        ])->create();

        KeyValueObject::factory()->withKey('service')->withValue([
            'name' => 'Premium Item',
            'description' => '24/7 technical support service',
            'price' => 15000,
            'address' => [
                'street' => '789 Support Lane',
                'city' => 'Helpdesk City',
                'country' => 'Serviceland',
            ],
            'active' => true,
        ])->create();
    }
}
