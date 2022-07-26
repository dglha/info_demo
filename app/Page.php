<?php

namespace App;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    //
    use Uuids;

    protected $primaryKey = 'id';

    protected $fillable = [
        'keyword', 'image', 'url', 'traffic_per_day',
        'traffic_sum', 'status', 'price', 'price_per_traffic',
        'traffic_remain', 'timeout'
    ];

    public $incrementing = false;

    protected $dates = [
        'timeout',
    ];

    protected $casts = [
        'timeout' => 'timestamp',
    ];
}
