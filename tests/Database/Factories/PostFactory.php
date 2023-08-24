<?php

namespace ChrisRhymes\LinkChecker\Test\Database\Factories;

use ChrisRhymes\LinkChecker\Test\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;

class PostFactory extends Factory
{
    protected $model = Post::class;

    public function definition()
    {
        return [
            'title' => $this->faker->words(3, true),
            'content' => $this->faker->text(),
            'url' => $this->faker->url(),
        ];
    }
}
