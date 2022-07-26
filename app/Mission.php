<?php

namespace App;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Model;

class Mission extends Model
{
    //
    use Uuids;

    protected $primaryKey = 'id';

    protected $fillable = [
        'user_id', 'page_id', 'code', 'status', 'ip', 'user_agent'
    ];

    // Tell eloquent not auto insert updated_at
    const UPDATED_AT = null;

    // Set incrementing to False -> Custom primary key -> Not return 0 when using Eloquent Laravel model
    public $incrementing = false;
}
