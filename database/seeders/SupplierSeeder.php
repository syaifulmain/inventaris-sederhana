<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Supplier;

class SupplierSeeder extends Seeder
{
    public function run()
    {
        $britpopNames = [
            'Oasis Enterprises', 'Blur Solutions', 'Pulp Co', 'Suede Ltd', 'Elastica Corp',
            'Verve Ventures', 'Stone Roses Inc', 'Cast Ltd', 'Shed Seven Corp', 'James Co',
            'Sleeper Solutions', 'Supergrass Ltd', 'Menswear Inc', 'Ocean Colour Co', 'Gene Corp',
            'Ash Ltd', 'Kula Shaker Enterprises', 'The Charlatans Co', 'Inspiral Carpets Inc', 'Dodgy Ltd',
            'Embrace Co', 'Space Ltd', 'Placebo Corp', 'Elbow Enterprises', 'Radiohead Solutions',
            'Suede Co', 'Blur Co', 'Oasis Co', 'Pulp Ventures', 'Geneva Ltd'
        ];

        $flowerStreets = [
            'Rose Street', 'Lily Avenue', 'Tulip Road', 'Daisy Lane', 'Orchid Drive',
            'Sunflower Boulevard', 'Lavender Way', 'Violet Street', 'Marigold Road', 'Jasmine Avenue',
            'Hibiscus Lane', 'Carnation Street', 'Peony Drive', 'Magnolia Road', 'Iris Avenue',
            'Gardenia Street', 'Azalea Lane', 'Camellia Drive', 'Buttercup Road', 'Daffodil Street',
            'Freesia Lane', 'Hyacinth Drive', 'Lotus Road', 'Primrose Street', 'Poppy Lane',
            'Bluebell Drive', 'Chrysanthemum Road', 'Petunia Street', 'Ranunculus Lane', 'Snapdragon Drive'
        ];

        for ($i = 0; $i < 30; $i++) {
            // SUP-003 sampai SUP-032
            Supplier::create([
                'code' => 'SUP-' . str_pad($i + 3, 3, '0', STR_PAD_LEFT), 
                'name' => $britpopNames[$i],
                'address' => "Jl. " . $flowerStreets[$i] . " No. " . ($i + 3),
            ]);
        }
    }
}
