# Link Checker for Laravel

A package that will check for broken links in the HTML of a specified model's fields.

## Getting Started

```bash
composer require chrisrhymes/link-checker
```

### Migrate the database

```bash
php artisan migrate
```

### Add the Trait to your models

Add the HasBrokenLinks trait to your model

```php
<?php

namespace App\Models;

use ChrisRhymes\LinkChecker\Traits\HasBrokenLinks;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory, HasBrokenLinks;
}
```

## Usage

Then you can check if the model has broken links in the html in a specific field.

```php
$post = Post::first();

CheckModelForBrokenLinks::dispatch($post, ['content']);
```

This will queue a job to get the links from the model, which will then queue a job to check each link it finds.

You will then need to run the queue to run the checks.

```bash
php artisan queue:work
```

It checks the link using the Laravel Http client `Http::get($link)->failed()` and if it fails it is determined to be a broken link.

Any broken links will be stored in the broken_links table, with a polymorphic relationship back to the original model.

```php
$post = Post::first();

$post->brokenLinks; // A collection of broken links for the model

$post->brokenLinks[0]->broken_link; // The link that is broken
```

## Tests

The tests are built with [Pest](https://pestphp.com/).

Run the tests using either of the below commands.

```bash
vendor/bin/pest

// Or

composer test
```
