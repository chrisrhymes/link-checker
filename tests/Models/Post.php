<?php

namespace ChrisRhymes\LinkChecker\Test\Models;

use ChrisRhymes\LinkChecker\Test\Database\Factories\PostFactory;
use ChrisRhymes\LinkChecker\Traits\HasBrokenLinks;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory, HasBrokenLinks;

    protected $fillable = [
        'title',
        'content,',
    ];

    protected static function newFactory()
    {
        return PostFactory::new();
    }
}
