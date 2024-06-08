<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
        \App\Models\User::create([
            'name' => 'Filip',
            'surname' => 'Głowacki',
            'phone_number' => '123456789',
            'email' => 'michaldariusznowakowski@proton.me',
            'role' => 'dentist',
            'active' => true,
            'password' => bcrypt('12345678'),
            'public_key' => null,
            'email_verified_at' => '2021-10-12 00:00:00',
            'verified' => true,
        ]);
        \App\Models\User::create([
            'name' => 'Grzegorz',
            'surname' => 'Szymański',
            'phone_number' => '123456789',
            'email' => 'michaldariusznowakowski+grzegorz@proton.me',
            'role' => 'dentist',
            'active' => true,
            'password' => bcrypt('12345678'),
            'public_key' => null,
            'email_verified_at' => '2021-10-12 00:00:00',
            'verified' => true,
        ]);
        \App\Models\User::create([
            'name' => 'Alicja',
            'surname' => 'Majewska',
            'phone_number' => '123456789',
            'email' => 'michaldariusznowakowski+alicja@proton.me',
            'role' => 'dentist',
            'active' => true,
            'password' => bcrypt('12345678'),
            'public_key' => null,
            'email_verified_at' => '2021-10-12 00:00:00',
            'verified' => true,
        ]);
        \App\Models\User::create([
            'name' => 'Karol',
            'surname' => 'Wójcik',
            'phone_number' => '123456789',
            'email' => 'michaldariusznowakowski+karol@proton.me',
            'role' => 'dentist',
            'active' => true,
            'password' => bcrypt('12345678'),
            'public_key' => null,
            'email_verified_at' => '2021-10-12 00:00:00',
            'verified' => true,
        ]);
        \App\Models\User::create([
            'name' => 'Bartosz',
            'surname' => 'Olszewski',
            'phone_number' => '123456789',
            'email' => 'michaldariusznowakowski+bartosz@proton.me',
            'role' => 'dentist',
            'active' => true,
            'password' => bcrypt('12345678'),
            'public_key' => null,
            'email_verified_at' => '2021-10-12 00:00:00',
            'verified' => true,
        ]);
        \App\Models\User::create([
            'name' => 'Klaudia',
            'surname' => 'Witkowska',
            'phone_number' => '123456789',
            'email' => 'michaldariusznowakowski+klaudia@proton.me',
            'role' => 'dentist',
            'active' => true,
            'password' => bcrypt('12345678'),
            'public_key' => null,
            'email_verified_at' => '2021-10-12 00:00:00',
            'verified' => true,
        ]);

        \App\Models\Offer::create([
            'doctor_id' => 1,
        ]);
        \App\Models\Offer::create([
            'doctor_id' => 2,
        ]);
        \App\Models\Offer::create([
            'doctor_id' => 3,
        ]);
        \App\Models\Offer::create([
            'doctor_id' => 4,
        ]);
        \App\Models\Offer::create([
            'doctor_id' => 5,
        ]);
        \App\Models\Offer::create([
            'doctor_id' => 6,
        ]);

        \App\Models\User::create([
            'name' => 'Michał',
            'surname' => 'Nowakowski',
            'phone_number' => '123456789',
            'email' => 'michaldariusznowakowski@protonmail.com',
            'role' => 'patient',
            'active' => true,
            'password' => bcrypt('12345678'),
            'public_key' => null,
            'email_verified_at' => '2021-10-12 00:00:00',
            'verified' => true,
        ]);
    }
}
