<?php

namespace ChrisRhymes\LinkChecker\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BrokenLink extends Model
{
    use HasFactory;

    protected $fillable = [
        'linkable_id',
        'linkable_type',
        'broken_link',
    ];

    public function linkable()
    {
        return $this->morphTo();
    }
}
