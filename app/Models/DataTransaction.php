<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\UsesUuid;

class DataTransaction extends Model
{
    use UsesUuid;
    protected $guarded = [];

    protected $with = [
        'service', 'bundle'
    ];

    const CREATED_AT = 'date_created';
    const UPDATED_AT = 'date_modified';

    public function service() {
        return $this->belongsTo(Service::class);
    }

    public function bundle() {
        return $this->belongsTo(DataBundle::class, 'data_bundles_id', 'id');
    }
}
