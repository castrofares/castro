<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Database\Seeders\SuperAdminSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // تسجيل Seeder للسوبر أدمن
        $this->call(SuperAdminSeeder::class);
    }
}
