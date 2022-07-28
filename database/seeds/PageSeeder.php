<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

class PageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            [
                'id' => Uuid::uuid4()->toString(),
                'keyword' => 'demo',
                'image' => 'tenor.gif',
                'url' => 'http://localhost',
                'traffic_per_day' => 100,
                'traffic_sum' => 1000,
                'traffic_remain' => 1000,
                'onsite' => 30,
                'status' => 1,
                'timeout' => '02:00:00'
            ],
            [
                'id' => Uuid::uuid4()->toString(),
                'keyword' => 'demo 1',
                'image' => 'tenor.gif',
                'url' => 'http://localhost/abcasdf',
                'traffic_per_day' => 100,
                'traffic_sum' => 1000,
                'traffic_remain' => 1000,
                'onsite' => 15,
                'status' => 1,
                'timeout' => '02:00:00'
            ]
        ];

        DB::table('pages')->insert($data);
    }
}
