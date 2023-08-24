<?php

use ChrisRhymes\LinkChecker\Test\Models\Post;

beforeEach(function () {
    $this->post = Post::factory()
        ->create([
            'content' => '
                <a href="https://a-broken-link">Broken link</a>',
        ]);
});

it('has brokenLinks relationship', function () {
    expect($this->post->brokenLinks)->toHaveCount(0);

    $this->post->brokenLinks()->create([
        'broken_link' => 'https://a-broken-link',
        'link_text' => 'broken link text',
        'exception_message' => 'An exception message',
    ]);

    expect($this->post->fresh()->brokenLinks)->toHaveCount(1);

    expect($this->post->fresh()->brokenLinks[0])
        ->broken_link->toBe('https://a-broken-link')
        ->link_text->toBe('broken link text')
        ->exception_message->toBe('An exception message');
});
