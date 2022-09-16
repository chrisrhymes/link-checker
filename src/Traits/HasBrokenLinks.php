<?php

namespace ChrisRhymes\LinkChecker\Traits;

use ChrisRhymes\LinkChecker\Models\BrokenLink;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasBrokenLinks
{
    public function brokenLinks(): MorphMany
    {
        return $this->morphMany(BrokenLink::class, 'linkable');
    }
}
