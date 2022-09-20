<?php

namespace ChrisRhymes\LinkChecker\Facades;

class LinkChecker extends \Illuminate\Support\Facades\Facade
{
    /**
     * {@inheritDoc}
     */
    protected static function getFacadeAccessor()
    {
        return \ChrisRhymes\LinkChecker\LinkChecker::class;
    }
}
