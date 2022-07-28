<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

class InfoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
         ['id' => Uuid::uuid4()->toString(),'link' => 'google.com'],
         ['id' => Uuid::uuid4()->toString(),'link' => 'facebook.com'],
         ['id' => Uuid::uuid4()->toString(),'link' => 'youtube.com'],
         ['id' => Uuid::uuid4()->toString(),'link' => 'genk.vn'],
         ['id' => Uuid::uuid4()->toString(),'link' => 'baomoi.com'],
        ];

        DB::table('infos')->insert($data);
    }
}
