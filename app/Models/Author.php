<?php

namespace App\Models;

use App\Support\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Author extends Model
{
    use HasUuid, SoftDeletes;

    protected $fillable = ['name', 'slug', 'bio'];

    public function books(): BelongsToMany
    {
        return $this->belongsToMany(Book::class);
    }
}
