<?php

use ChrisRhymes\LinkChecker\Jobs\CheckModelForBrokenLinks;
use ChrisRhymes\LinkChecker\Test\Models\Post;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    Http::fake([
        'https://this-is-a-temporary-redirect.com' => Http::response(null, 302),
        'https://www.this-is-a-permanent-redirect.co.uk' => Http::response(null, 301),
    ]);

    $this->post = Post::factory()
        ->create([
            'content' => '
                <a href="https://this-is-a-temporary-redirect.com">Temporary redirect link</a>
                <p>Some other content here</p>
                <a href="https://www.this-is-a-permanent-redirect.co.uk">Permanent redirect link</a>',
        ]);
});

it('records temporary and permanent redirects', function () {
    CheckModelForBrokenLinks::dispatch($this->post, ['content']);

    $this->assertDatabaseHas('broken_links', [
        'linkable_id' => $this->post->id,
        'linkable_type' => 'ChrisRhymes\LinkChecker\Test\Models\Post',
        'broken_link' => 'https://this-is-a-temporary-redirect.com',
        'link_text' => 'Temporary redirect link',
        'exception_message' => '302 Redirect',
    ]);

    $this->assertDatabaseHas('broken_links', [
        'linkable_id' => $this->post->id,
        'linkable_type' => 'ChrisRhymes\LinkChecker\Test\Models\Post',
        'broken_link' => 'https://www.this-is-a-permanent-redirect.co.uk',
        'link_text' => 'Permanent redirect link',
        'exception_message' => '301 Redirect',
    ]);
});
