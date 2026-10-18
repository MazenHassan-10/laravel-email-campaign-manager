<?php

namespace Database\Seeders;

use App\Models\Lead;
use Illuminate\Database\Seeder;

class LeadSeeder extends Seeder
{
    private array $countries = ['Egypt', 'Saudi Arabia', 'UAE', 'United States'];

    private array $statuses = ['new', 'contacted', 'interested', 'converted'];

    public function run(): void
    {
        $rows = [];

        for ($i=0; $i < 1000; $i++) {
            $rows[]=[
                'name'=> fake()->name(),
                'email' => fake()->unique()->safeEmail(),
                'status' => fake()->randomElement($this->statuses),
                'country' => fake()->randomElement($this->countries),
                'birthdate' => fake()->dateTimeBetween('-60 years' , '-18 years')->format('Y-m-d'),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        foreach (array_chunk($rows , 500) as $chunk) {
            Lead::insert($chunk);
        }
    }
}
