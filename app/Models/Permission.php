<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $guarded = [];

    protected $connection = 'mysql';
    // protected $connection = 'mysql';

    protected $table = "permissions";
    public function role() {
        return $this->belongsTo(Role::class);
    }
}
