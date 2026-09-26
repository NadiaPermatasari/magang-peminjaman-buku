<?php

namespace App\Models;

use App\Support\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Publisher extends Model
{
    use HasFactory, HasUuid, SoftDeletes;

    protected $fillable = ['name', 'slug', 'address', 'phone'];

    public function books(): HasMany
    {
        return $this->hasMany(Book::class);
    }
}
