<?php

use ChrisRhymes\LinkChecker\Facades\LinkChecker;
use ChrisRhymes\LinkChecker\Jobs\CheckModelForBrokenLinks;
use ChrisRhymes\LinkChecker\Test\Models\Post;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    Http::fake([
        'https://this-is-a-relative-link.com/relative' => Http::response(null, 302),
    ]);

    $this->post = Post::factory()
        ->create([
            'content' => '
                <a href="relative">Temporary redirect link</a>',
        ]);
});

it('records relative urls using base url', function () {
    CheckModelForBrokenLinks::dispatch($this->post, ['content'], 'https://this-is-a-relative-link.com/');

    $this->assertDatabaseHas('broken_links', [
        'linkable_id' => $this->post->id,
        'linkable_type' => 'ChrisRhymes\LinkChecker\Test\Models\Post',
        'broken_link' => 'https://this-is-a-relative-link.com/relative',
        'link_text' => 'Temporary redirect link',
        'exception_message' => '302 Redirect',
    ]);
});

it('records relative urls using base url using the facade', function () {
    LinkChecker::checkForBrokenLinks($this->post, ['content'], 'https://this-is-a-relative-link.com/');

    $this->assertDatabaseHas('broken_links', [
        'linkable_id' => $this->post->id,
        'linkable_type' => 'ChrisRhymes\LinkChecker\Test\Models\Post',
        'broken_link' => 'https://this-is-a-relative-link.com/relative',
        'link_text' => 'Temporary redirect link',
        'exception_message' => '302 Redirect',
    ]);
});

it('records curl error for relative urls without setting base', function () {
    CheckModelForBrokenLinks::dispatch($this->post, ['content']);

    $this->assertDatabaseHas('broken_links', [
        'linkable_id' => $this->post->id,
        'linkable_type' => 'ChrisRhymes\LinkChecker\Test\Models\Post',
        'broken_link' => 'relative',
        'link_text' => 'Temporary redirect link',
        'exception_message' => 'URI must include a scheme and host. Use an absolute URI, a network-path reference starting with //, or configure a base_uri.',
    ]);
});

it('records curl error for relative urls without setting base using the facade', function () {
    LinkChecker::checkForBrokenLinks($this->post, ['content']);

    $this->assertDatabaseHas('broken_links', [
        'linkable_id' => $this->post->id,
        'linkable_type' => 'ChrisRhymes\LinkChecker\Test\Models\Post',
        'broken_link' => 'relative',
        'link_text' => 'Temporary redirect link',
        'exception_message' => 'URI must include a scheme and host. Use an absolute URI, a network-path reference starting with //, or configure a base_uri.',
    ]);
});
