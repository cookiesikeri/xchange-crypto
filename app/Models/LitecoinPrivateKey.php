<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LitecoinPrivateKey extends Model
{
    use HasFactory;
    protected $guarded = [];

    protected $table = "litecoin_private_keys";

    public function user(){
        return $this->belongsTo(User::class);
    }
}
