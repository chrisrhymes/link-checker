<?php

use ChrisRhymes\LinkChecker\Jobs\CheckModelForBrokenLinks;
use ChrisRhymes\LinkChecker\Models\BrokenLink;
use ChrisRhymes\LinkChecker\Test\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

beforeEach(function () {
    Http::fake([
        '*' => Http::response(null, 404),
    ]);
});

it('sets verify ssl to false', function () {
    Config::set('link-checker.verify', false);

    $post = Post::factory()
        ->create([
            'content' => '
                <a href="https://this-is-broken.com/test1">Broken link</a>',
        ]);

    CheckModelForBrokenLinks::dispatch($post, ['content']);

    Http::assertSent(function (Request $request) {
        return $request->url('https://this-is-broken.com/test1');
    });

    expect(BrokenLink::get())
        ->toHaveCount(1)
        ->first()->broken_link->toBe('https://this-is-broken.com/test1');
});
