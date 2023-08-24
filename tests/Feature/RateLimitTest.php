<?php

use ChrisRhymes\LinkChecker\Jobs\CheckModelForBrokenLinks;
use ChrisRhymes\LinkChecker\Models\BrokenLink;
use ChrisRhymes\LinkChecker\Test\Models\Post;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    Http::fake([
        '*' => Http::response(null, 404),
    ]);
});

it('triggers the rate limit and only finds one broken link', function () {
    Config::set('link-checker.rate_limit', 1);

    $post = Post::factory()
        ->create([
            'content' => '
                <a href="https://this-is-broken.com/test1">Broken link</a>
                <p>Some other content here</p>
                <a href="https://this-is-broken.com/test2">Broken link</a>
                <a href="https://this-is-broken.com/test3">Broken link</a>',
        ]);

    CheckModelForBrokenLinks::dispatch($post, ['content']);

    expect(BrokenLink::get())
        ->toHaveCount(1)
        ->first()->broken_link->toBe('https://this-is-broken.com/test1');
});
