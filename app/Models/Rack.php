<?php

namespace App\Models;

use App\Support\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Rack extends Model
{
    use HasUuid, SoftDeletes;

    protected $fillable = ['code', 'name', 'location'];
}
