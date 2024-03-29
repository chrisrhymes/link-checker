<?php

namespace ChrisRhymes\LinkChecker\Jobs;

use ChrisRhymes\LinkChecker\Objects\Link;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\RateLimited;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class CheckLinkFailed implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Link $link;

    private Model $model;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 30;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Model $model, Link $link)
    {
        $this->link = $link;
        $this->model = $model;
    }

    /**
     * Get the middleware the job should pass through.
     *
     * @return array
     */
    public function middleware()
    {
        return [new RateLimited('link-checker')];
    }

    /**
     * Determine the time at which the job should timeout.
     *
     * @return \DateTime
     */
    public function retryUntil()
    {
        return now()->addMinutes(config('link-checker.retry_until', 10));
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Prevent unnecessary request for empty links
        if (empty($this->link->url)) {
            $this->model->brokenLinks()
                ->create([
                    'broken_link' => $this->link->url,
                    'link_text' => $this->link->text,
                    'exception_message' => 'Empty link',
                ]);

            return;
        }

        // Prevent checking mailto and tel links
        if (Str::startsWith($this->link->url, ['mailto', 'tel'])) {
            return;
        }

        try {
            $response = Http::withUserAgent(config('link-checker.user_agent', 'link-checker'))
                ->withOptions([
                    'verify' => config('link-checker.verify', true),
                ])
                ->timeout(config('link-checker.timeout', 10))
                ->get($this->link->url);

            if ($response->redirect()) {
                $this->model->brokenLinks()
                    ->create([
                        'broken_link' => $this->link->url,
                        'link_text' => $this->link->text,
                        'exception_message' => "{$response->status()} Redirect",
                    ]);
            }

            if ($response->failed()) {
                $this->model->brokenLinks()
                    ->create([
                        'broken_link' => $this->link->url,
                        'link_text' => $this->link->text,
                        'exception_message' => "{$response->status()} Status Code",
                    ]);
            }
        } catch (Exception $e) {
            $this->model->brokenLinks()
                ->create([
                    'broken_link' => $this->link->url,
                    'link_text' => $this->link->text,
                    'exception_message' => $e->getMessage(),
                ]);
        }
    }
}
