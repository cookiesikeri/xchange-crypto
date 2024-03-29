<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\UsesUuid;

class Notification extends Model
{
    use UsesUuid;
    protected $table ="notifications";

    protected $fillable = ['title', 'message', 'status'];
}
