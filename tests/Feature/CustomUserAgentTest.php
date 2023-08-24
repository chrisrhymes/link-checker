<?php

use ChrisRhymes\LinkChecker\Jobs\CheckModelForBrokenLinks;
use ChrisRhymes\LinkChecker\Test\Models\Post;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    Http::fake();

    $this->post = Post::factory()
        ->create([
            'content' => ' <a href="https://this-is-broken.com">Broken link</a>',
        ]);
});

it('sets a default user agent for requests', function () {
    CheckModelForBrokenLinks::dispatch($this->post, ['content']);

    Http::assertSent(function (Request $request) {
        return $request->hasHeader('User-Agent', 'link-checker');
    });
});

it('sets a custom user agent for requests', function () {
    Config::set('link-checker.user_agent', 'custom-user-agent');

    CheckModelForBrokenLinks::dispatch($this->post, ['content']);

    Http::assertSent(function (Request $request) {
        return $request->hasHeader('User-Agent', 'custom-user-agent');
    });
});
