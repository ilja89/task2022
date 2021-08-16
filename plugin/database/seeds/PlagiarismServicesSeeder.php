<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PlagiarismServicesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $services = [
            ['code' => 1, 'name' => 'jplag'],
            ['code' => 2, 'name' => 'moss'],
        ];

        foreach ($services as $service) {
            DB::table('charon_plagiarism_service')->insert($service);
        }
    }
}
