<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{

    protected $connection = 'mysql';

    protected $guarded = [];

    protected $table = "roles";


    public function admins() {
        return $this->hasMany(Admin::class, 'role_id');
    }

    public function permission() {
        return $this->hasOne(Permission::class, 'id');
    }


}
