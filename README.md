# Link Checker for Laravel

A package that will check for broken links in the HTML of a specified model's fields.

## Contents

- [Getting Started](#getting-started)
- [Usage](#usage)
- [Rate Limiting](#rate-limiting)
- [Tests](#tests)

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

### Publish the config (optional)

By default, the timeout for link checks is set to 10 seconds. There are also settings for the rate limiting.

If you wish to change this then publish the configuration file and update the values.

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

## Rate Limiting

In order to reduce the amount of requests sent to a domain at a time, this package has rate limiting enabled.

The configuration file allows you to set the `rate_limit` to set how many requests can be sent to a single domain within a minute. The default is set to 5, so adjust as required for your circumstances.

The configuration file also allows you to set the `retry_until` so the job will be retried until the time limit (in munites) is reached.

## Tests

The tests are built with [Pest](https://pestphp.com/).

Run the tests using either of the below commands.

```bash
vendor/bin/pest

// Or

composer test
```
