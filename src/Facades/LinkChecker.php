<?php

namespace ChrisRhymes\LinkChecker\Facades;

use Illuminate\Support\Facades\Facade;

class LinkChecker extends Facade
{
    /**
     * {@inheritDoc}
     */
    protected static function getFacadeAccessor()
    {
        return \ChrisRhymes\LinkChecker\LinkChecker::class;
    }
}
