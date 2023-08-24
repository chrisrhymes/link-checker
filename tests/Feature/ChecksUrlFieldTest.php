<?php

use ChrisRhymes\LinkChecker\Jobs\CheckModelForBrokenLinks;
use ChrisRhymes\LinkChecker\Models\BrokenLink;
use ChrisRhymes\LinkChecker\Test\Models\Post;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    Http::fake([
        'https://this-is-broken.com' => Http::response(null, 404),
    ]);

    $this->post = Post::factory()
        ->create([
            'content' => '<a href="https://this-is-broken.com">Broken link</a>',
            'url' => 'https://this-is-broken.com',
        ]);
});

it('checks a url not in html', function () {
    CheckModelForBrokenLinks::dispatch($this->post, ['url']);

    $this->assertDatabaseHas('broken_links', [
        'linkable_id' => $this->post->id,
        'linkable_type' => 'ChrisRhymes\LinkChecker\Test\Models\Post',
        'broken_link' => 'https://this-is-broken.com',
        'link_text' => 'https://this-is-broken.com',
        'exception_message' => '404 Status Code',
    ]);
});

it('checks for broken links in both specified fields', function () {
    CheckModelForBrokenLinks::dispatch($this->post, ['content', 'url']);

    expect(BrokenLink::count())
        ->toEqual(2);

    $this->assertDatabaseHas('broken_links', [
        'linkable_id' => $this->post->id,
        'linkable_type' => 'ChrisRhymes\LinkChecker\Test\Models\Post',
        'broken_link' => 'https://this-is-broken.com',
        'link_text' => 'https://this-is-broken.com',
        'exception_message' => '404 Status Code',
    ]);

    $this->assertDatabaseHas('broken_links', [
        'linkable_id' => $this->post->id,
        'linkable_type' => 'ChrisRhymes\LinkChecker\Test\Models\Post',
        'broken_link' => 'https://this-is-broken.com',
        'link_text' => 'Broken link',
        'exception_message' => '404 Status Code',
    ]);
});

it('ignores invalid url value', function () {
    $postWithInvalidUrl = Post::factory()->create([
        'url' => 'not a link',
    ]);

    CheckModelForBrokenLinks::dispatch($postWithInvalidUrl, ['url']);

    Http::assertNothingSent();

    expect(BrokenLink::count())
        ->toEqual(0);
});
