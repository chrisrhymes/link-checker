<?php

namespace ChrisRhymes\LinkChecker\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * ChrisRhymes\LinkChecker\Models\BrokenLink
 *
 * @property int $id
 * @property int $linkable_id
 * @property string $linkable_type
 * @property string $broken_link
 * @property string $link_text
 * @property string $exception_message
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent|null $linkable
 */
class BrokenLink extends Model
{
    use HasFactory;

    protected $fillable = [
        'linkable_id',
        'linkable_type',
        'broken_link',
        'link_text',
        'exception_message',
    ];

    public function linkable(): MorphTo
    {
        return $this->morphTo();
    }
}
