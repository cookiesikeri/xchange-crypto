<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\UsesUuid;

class State extends Model
{
    use UsesUuid;

    protected $table ="states";
    protected $guarded = [
        'id'
    ];
}
