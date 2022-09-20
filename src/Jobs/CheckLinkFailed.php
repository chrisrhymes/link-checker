<?php

namespace ChrisRhymes\LinkChecker\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class CheckLinkFailed implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private string $link;

    private Model $model;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Model $model, string $link)
    {
        $this->link = $link;
        $this->model = $model;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $failed = Http::timeout(config('link-checker.timeout', 10))
                ->get($this->link)->failed();

            if ($failed) {
                $this->model->brokenLinks()
                    ->create([
                        'broken_link' => $this->link,
                    ]);
            }
        } catch (Exception $e) {
            $this->model->brokenLinks()
                ->create([
                    'broken_link' => $this->link,
                    'exception_message' => $e->getMessage(),
                ]);
        }
        
    }
}
