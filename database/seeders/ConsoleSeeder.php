<?php

namespace Database\Seeders;

use App\Models\Console;
use Illuminate\Database\Seeder;

class ConsoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create sample consoles
        $consoles = [
            [
                'name' => 'PlayStation 5',
                'cpu' => 'AMD Zen 2 (8x 3.5GHz)',
                'gpu' => 'AMD RDNA 2 (10.3 TFLOPS)',
                'psu' => '350W',
                'ram' => '16GB GDDR6',
                'hdd' => null,
                'ssd' => '825GB Custom SSD',
                'description' => 'Sony\'s fifth home video game console released in November 2020.',
                'is_active' => true,
            ],
            [
                'name' => 'Xbox Series X',
                'cpu' => 'AMD Zen 2 (8x 3.8GHz)',
                'gpu' => 'AMD RDNA 2 (12 TFLOPS)',
                'psu' => '315W',
                'ram' => '16GB GDDR6',
                'hdd' => null,
                'ssd' => '1TB Custom NVMe SSD',
                'description' => 'Microsoft\'s fourth generation Xbox console released in November 2020.',
                'is_active' => true,
            ],
            [
                'name' => 'Nintendo Switch',
                'cpu' => 'NVIDIA Tegra X1',
                'gpu' => 'NVIDIA Maxwell (0.5 TFLOPS)',
                'psu' => '39W',
                'ram' => '4GB LPDDR4',
                'hdd' => null,
                'ssd' => '32GB eMMC',
                'description' => 'Nintendo\'s hybrid console that can be used as both a home console and a portable device, released in March 2017.',
                'is_active' => true,
            ],
            [
                'name' => 'PlayStation 4 Pro',
                'cpu' => 'AMD Jaguar (8x 2.13GHz)',
                'gpu' => 'AMD Radeon (4.2 TFLOPS)',
                'psu' => '310W',
                'ram' => '8GB GDDR5',
                'hdd' => '1TB',
                'ssd' => null,
                'description' => 'Enhanced version of the PlayStation 4 with 4K gaming support, released in November 2016.',
                'is_active' => true,
            ],
            [
                'name' => 'Xbox One X',
                'cpu' => 'AMD Jaguar (8x 2.3GHz)',
                'gpu' => 'AMD Polaris (6 TFLOPS)',
                'psu' => '245W',
                'ram' => '12GB GDDR5',
                'hdd' => '1TB',
                'ssd' => null,
                'description' => 'Enhanced version of the Xbox One with 4K gaming support, released in November 2017.',
                'is_active' => true,
            ],
            [
                'name' => 'Nintendo Wii U',
                'cpu' => 'IBM PowerPC (3x 1.24GHz)',
                'gpu' => 'AMD Radeon (0.35 TFLOPS)',
                'psu' => '75W',
                'ram' => '2GB DDR3',
                'hdd' => '32GB',
                'ssd' => null,
                'description' => 'Nintendo\'s sixth home console and the successor to the Wii, released in November 2012.',
                'is_active' => false,
            ],
            [
                'name' => 'PlayStation 3',
                'cpu' => 'Cell Broadband Engine (3.2GHz)',
                'gpu' => 'NVIDIA RSX (0.4 TFLOPS)',
                'psu' => '250W',
                'ram' => '256MB XDR DRAM',
                'hdd' => '500GB',
                'ssd' => null,
                'description' => 'Sony\'s third home video game console, released in November 2006.',
                'is_active' => false,
            ],
            [
                'name' => 'Xbox 360',
                'cpu' => 'IBM PowerPC (3x 3.2GHz)',
                'gpu' => 'ATI Xenos (0.24 TFLOPS)',
                'psu' => '175W',
                'ram' => '512MB GDDR3',
                'hdd' => '250GB',
                'ssd' => null,
                'description' => 'Microsoft\'s second video game console, released in November 2005.',
                'is_active' => false,
            ],
            [
                'name' => 'Nintendo Wii',
                'cpu' => 'IBM PowerPC (1x 729MHz)',
                'gpu' => 'ATI Hollywood (0.12 TFLOPS)',
                'psu' => '40W',
                'ram' => '88MB',
                'hdd' => null,
                'ssd' => null,
                'description' => 'Nintendo\'s fifth home video game console, released in November 2006.',
                'is_active' => false,
            ],
            [
                'name' => 'Sega Dreamcast',
                'cpu' => 'Hitachi SH-4 (200MHz)',
                'gpu' => 'PowerVR2 CLX2',
                'psu' => '45W',
                'ram' => '16MB',
                'hdd' => null,
                'ssd' => null,
                'description' => 'Sega\'s final home video game console, released in 1998.',
                'is_active' => false,
            ],
        ];

        foreach ($consoles as $consoleData) {
            Console::create($consoleData);
        }
    }
}
