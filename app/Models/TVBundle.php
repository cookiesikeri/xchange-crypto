<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\UsesUuid;

class TVBundle extends Model
{
    use UsesUuid;
    protected $table = "tv_bundles";
    protected $guarded = [];
    CONST CREATED_AT = 'date_created';
    CONST UPDATED_AT = 'date_modified';

    public function tvTransactions() {
        return $this->hasMany(TVTransaction::class, 'id', 'tv_bundles_id');
    }
}
