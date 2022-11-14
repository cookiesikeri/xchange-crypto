<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\UsesUuid;

class UserSecretQAndA extends Model
{
    use HasFactory, UsesUuid;

    protected $guarded = [];

    protected $table = 'user_secret_q_and_a_s';

    public function user(){
        return $this->belongsTo(User::class);
    }
}
