# Link Checker for Laravel

A package that will check for broken links in the specified model's fields. It will check both URL fields and fields containing HTML.

![Downloads](https://img.shields.io/packagist/dt/chrisrhymes/link-checker.svg)
![Downloads](https://img.shields.io/github/stars/chrisrhymes/link-checker.svg)

## Contents

- [Getting Started](#getting-started)
- [Usage](#usage)
  - [Relative links](#relative-links)
- [Rate Limiting](#rate-limiting)
- [User Agent](#user-agent)
- [Verify SSL](#verify-ssl)
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

Then you can check if the model has broken links in the specified field(s).

```php
use ChrisRhymes\LinkChecker\Jobs\CheckModelForBrokenLinks;
use ChrisRhymes\LinkChecker\Facades\LinkChecker;

$post = Post::first();

// Dispatch the job directly
CheckModelForBrokenLinks::dispatch($post, ['content', 'url']);

// Or using the facade
LinkChecker::checkForBrokenLinks($post, ['content', 'url']);
```

This will queue a job to get the links from the model, which will then queue a job to check each link it finds.

The job will record an entry in the database for broken links with an empty url, but will skip testing mailto or tel links.

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

### Relative links

If you have relative links within a html field in your model (that don't begin with 'http'), then you can pass a 3rd parameter as the base. The CheckModelForBrokenLinks job will prepend the base to the relative url before it is checked.

If your relative links don't begin with `/`, then ensure your base parameter has a trailing slash, `'http://example.com/'`.

```php
use ChrisRhymes\LinkChecker\Jobs\CheckModelForBrokenLinks;
use ChrisRhymes\LinkChecker\Facades\LinkChecker;

$post = Post::first();

// Dispatch the job directly
CheckModelForBrokenLinks::dispatch($post, ['content', 'url'], 'http://example.com');

// Or using the facade
LinkChecker::checkForBrokenLinks($post, ['content', 'url'], 'http://example.com');
```

## Rate Limiting

In order to reduce the amount of requests sent to a domain at a time, this package has rate limiting enabled.

The configuration file allows you to set the `rate_limit` to set how many requests can be sent to a single domain within a minute. The default is set to 5, so adjust as required for your circumstances.

The configuration file also allows you to set the `retry_until` so the job will be retried until the time limit (in munites) is reached.

## User Agent

To set a custom user agent for requests sent by the link checker, set the `user_agent` in the configuration file. For example `'user_agent' => 'my-user-agent',`

The default value is `link-checker`.

## Verify SSL

To disable verifying the SSL certificate of the link you are checking, [publish the package configuration](#publish-the-config-optional) and then set `'verify' => false,`.

This uses the HTTP client withOptions() to set the [verify request option in Guzzle](https://docs.guzzlephp.org/en/stable/request-options.html#verify).

## Tests

The tests are built with [Pest](https://pestphp.com/).

Run the tests using either of the below commands.

```bash
vendor/bin/pest

// Or

composer test
```
