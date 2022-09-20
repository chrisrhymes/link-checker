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

## Publish the config (optional)

By default, the timeout for link checks is set to 10 seconds. If you wish to change this then publish the configuration file and update the values.

```bash
php artisan vendor:publish --provider="ChrisRhymes\LinkChecker\ServiceProvider"
```

## Usage

Then you can check if the model has broken links in the html in a specific field.

```php
use ChrisRhymes\LinkChecker\Jobs\CheckModelForBrokenLinks;
use ChrisRhymes\LinkChecker\Facades\LinkChecker;

$post = Post::first();

// Dispatch the job directly
CheckModelForBrokenLinks::dispatch($post, ['content']);

// Or using the facade
LinkChecker::checkForBrokenLinks($post, ['content']);
```

This will queue a job to get the links from the model, which will then queue a job to check each link it finds.

You will then need to run the queue to run the checks.

```bash
php artisan queue:work
```

It checks the link using the Laravel Http client `Http::get($link)->failed()` and if it fails it is determined to be a broken link.

Any broken links will be stored in the broken_links table, with a polymorphic relationship back to the original model.

If an exception is thrown, such as a timeout, then an exception_message will also be recorded in the broken_links table.

```php
$post = Post::first();

$post->brokenLinks; // A collection of broken links for the model

$post->brokenLinks[0]->broken_link; // The link that is broken
$post->brokenLinks[0]->exception_message; // The optional exception message
```

## Tests

The tests are built with [Pest](https://pestphp.com/).

Run the tests using either of the below commands.

```bash
vendor/bin/pest

// Or

composer test
```
