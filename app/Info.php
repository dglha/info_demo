<?php

namespace App;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Model;

class Info extends Model
{
    //
    use Uuids;

    protected $primaryKey = 'id';

    protected $fillable = [
        'link'
    ];

    // Set incrementing to False -> Custom primary key -> Not return 0 when using Eloquent Laravel model
    public $incrementing = false;

}
