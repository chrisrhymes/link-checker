<?php

use ChrisRhymes\LinkChecker\Jobs\CheckModelForBrokenLinks;
use ChrisRhymes\LinkChecker\Test\Models\Post;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    $this->post = Post::factory()
        ->create([
            'content' => '
                <a href="http://www.example.com">Visit us</a>
                <a href="http://www.test.com">Test site</a>',
        ]);
});

it('skips the specified status code', function (int $statusCode) {
    Http::fake([
        'http://www.example.com' => Http::response('404', 404),
        'http://www.test.com' => Http::response(null, $statusCode),
    ]);

    Config::set('link-checker.status_codes_to_skip', [$statusCode]);

    CheckModelForBrokenLinks::dispatch($this->post, ['content']);

    expect($this->post->fresh()->brokenLinks)->toHaveCount(1);

    $this->assertDatabaseHas('broken_links', [
        'link_text' => 'Visit us',
        'broken_link' => 'http://www.example.com',
    ]);

    $this->assertDatabaseMissing('broken_links', [
        'link_text' => 'Test site',
        'broken_link' => 'http://www.test.com',
    ]);
})->with([
    'forbidden' => 403,
    'Too many requests' => 429,
    'Gateway timeout' => 504,
]);
