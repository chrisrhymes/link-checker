<?php

use ChrisRhymes\LinkChecker\Jobs\CheckModelForBrokenLinks;
use ChrisRhymes\LinkChecker\Test\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

it('finds the broken link and reports it', function () {
    $post = Post::factory()
        ->create([
            'content' => '
                <a href="https://this-is-broken.com">Broken link</a>
                <p>Some other content here</p>
                <a href="https://www.this-is-working.co.uk">Working link</a>',
        ]);

    Http::fake([
        'https://this-is-broken.com' => Http::response(null, 404),
        'https://www.this-is-working.co.uk' => Http::response('Ok', 200),
    ]);

    CheckModelForBrokenLinks::dispatch($post, ['content']);

    $this->assertDatabaseHas('broken_links', [
        'linkable_id' => $post->id,
        'linkable_type' => 'ChrisRhymes\LinkChecker\Test\Models\Post',
        'broken_link' => 'https://this-is-broken.com',
    ]);

    $this->assertDatabaseMissing('broken_links', [
        'linkable_id' => $post->id,
        'linkable_type' => 'ChrisRhymes\LinkChecker\Test\Models\Post',
        'broken_link' => 'https://www.this-is-working.co.uk',
    ]);

    $this->assertCount(1, $post->fresh()->brokenLinks);
});
