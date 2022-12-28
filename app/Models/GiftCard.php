<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GiftCard extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function user(){
        return $this->belongsTo(User::class);
    }

      public function myGiftCard($id){
        return $this
        //->select('type', 'state', 'amount', 'currency', 'gan', 'gitcard_id', 'status', 'response', 'location_id', 'idempotency_key')
        ->where('user_id', $id)->first();
    }
}
