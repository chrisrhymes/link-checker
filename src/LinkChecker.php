<?php

namespace ChrisRhymes\LinkChecker;

use ChrisRhymes\LinkChecker\Jobs\CheckModelForBrokenLinks;
use Illuminate\Database\Eloquent\Model;

class LinkChecker
{
    public function checkForBrokenLinks(Model $model, array $fields)
    {
        CheckModelForBrokenLinks::dispatch($model, $fields);
    }
}