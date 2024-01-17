<?php

namespace ChrisRhymes\LinkChecker;

use ChrisRhymes\LinkChecker\Jobs\CheckModelForBrokenLinks;
use Illuminate\Database\Eloquent\Model;

class LinkChecker
{
    public function checkForBrokenLinks(Model $model, array $fields, ?string $base = null)
    {
        CheckModelForBrokenLinks::dispatch($model, $fields, $base);
    }
}
