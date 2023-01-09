<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\UsesUuid;
use Spatie\Searchable\Searchable;
use Spatie\Searchable\SearchResult;

class BankAccountDetail extends Model implements Searchable
{
    use HasFactory, UsesUuid;

    protected $guarded = [];

    public function getSearchResult(): SearchResult
    {
        return new \Spatie\Searchable\SearchResult(
            $this,
            $this->account_number
        );
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function bankDetails($userId)
    {
        return $this->where('user_id', $userId)->first();
    }
}
