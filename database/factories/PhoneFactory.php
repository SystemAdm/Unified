<?php

namespace Database\Factories;

use App\Models\Phone;
use Illuminate\Database\Eloquent\Factories\Factory;

class PhoneFactory extends Factory
{
    protected $model = Phone::class;

    public function definition(): array
    {
        // Generate a random Norwegian phone number
        $phoneNumber = '+47' . random_int(90000000, 99999999);

        // Create a new phone instance to parse the number
        $phone = new \App\Models\Phone();
        $phone->phone_number = $phoneNumber;

        return [
            'country_code' => $phone->country_code,
            'number' => $phone->number,
        ];
    }
}
