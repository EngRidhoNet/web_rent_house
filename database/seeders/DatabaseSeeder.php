<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Listing;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        User::factory()->create([
            'name' => 'Superadmin',
            'email' => 'admin@gmail.com',
            'role' => 'admin'
        ]);

        $users = User::factory(10)->create();
        $listings = Listing::factory()->count(10)->create();

        Transaction::factory(10)
            ->state(
                new Sequence(
                    fn(Sequence $sequence) => [
                        'user_id' => $users->random(),
                        'listing_id' => $listings->random(),
                    ],
                )
            )
            ->create();
    }
}
