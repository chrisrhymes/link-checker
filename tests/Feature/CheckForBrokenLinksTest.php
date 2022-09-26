<?php

use ChrisRhymes\LinkChecker\Facades\LinkChecker;
use ChrisRhymes\LinkChecker\Jobs\CheckModelForBrokenLinks;
use ChrisRhymes\LinkChecker\Models\BrokenLink;
use ChrisRhymes\LinkChecker\Test\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

beforeEach(function () {
    Http::fake([
        'https://this-is-broken.com' => Http::response(null, 404),
        'https://www.this-is-working.co.uk' => Http::response('Ok', 200),
    ]);

    $this->post = Post::factory()
        ->create([
            'content' => '
                <a href="https://this-is-broken.com">Broken link</a>
                <p>Some other content here</p>
                <a href="https://www.this-is-working.co.uk">Working link</a>',
        ]);
});

it('finds the broken link and reports it', function () {
    CheckModelForBrokenLinks::dispatch($this->post, ['content']);

    $this->assertDatabaseHas('broken_links', [
        'linkable_id' => $this->post->id,
        'linkable_type' => 'ChrisRhymes\LinkChecker\Test\Models\Post',
        'broken_link' => 'https://this-is-broken.com',
    ]);

    $this->assertDatabaseMissing('broken_links', [
        'linkable_id' => $this->post->id,
        'linkable_type' => 'ChrisRhymes\LinkChecker\Test\Models\Post',
        'broken_link' => 'https://www.this-is-working.co.uk',
    ]);

    expect($this->post->fresh()->brokenLinks)->toHaveCount(1);
});

it('reports the exception message', function () {
    Http::preventStrayRequests();

    $post = Post::factory()
        ->create([
            'content' => '
                <a href="https://this-is-exception.com">Broken link causes exception</a>',
        ]);

    CheckModelForBrokenLinks::dispatch($post, ['content']);

    $this->assertDatabaseHas('broken_links', [
        'linkable_id' => $post->id,
        'linkable_type' => 'ChrisRhymes\LinkChecker\Test\Models\Post',
        'broken_link' => 'https://this-is-exception.com',
        'exception_message' => 'Attempted request to [https://this-is-exception.com] without a matching fake.',
    ]);
});

it('uses the facade to check for broken links', function () {
    Queue::fake();

    LinkChecker::checkForBrokenLinks($this->post, ['content']);

    Queue::assertPushed(CheckModelForBrokenLinks::class);
});

it('handles empty links and prevents unnecessary requests', function () {
    $post = Post::factory()
        ->create([
            'content' => '<a href="">Empty link</a><a href="">Empty link</a>',
        ]);
    
    CheckModelForBrokenLinks::dispatch($post, ['content']);
    
    Http::assertNothingSent();

    expect(BrokenLink::get())
        ->toHaveCount(2)
        ->first()->exception_message->toBe('Empty link');
});