<?php

use ChrisRhymes\LinkChecker\Jobs\CheckModelForBrokenLinks;
use ChrisRhymes\LinkChecker\Test\Models\Post;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    Http::fake();

    $this->post = Post::factory()
        ->create([
            'content' => '
                <a href="mailto:test@example.com">Email us</a>
                <a href="tel:0123456789">Call us</a>',
        ]);
});

it('ignores mailto and tel links', function () {
    CheckModelForBrokenLinks::dispatch($this->post, ['content']);

    expect($this->post->fresh()->brokenLinks)->toHaveCount(0);

    Http::assertNothingSent();

    $this->assertDatabaseCount('broken_links', 0);
});

it('does not check tel and mailto links when base is added', function () {
    CheckModelForBrokenLinks::dispatch($this->post, ['content'], 'https://this-is-a-relative-link.com/');

    Http::assertNothingSent();

    $this->assertDatabaseCount('broken_links', 0);
});
