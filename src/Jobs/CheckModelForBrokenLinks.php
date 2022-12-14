<?php

namespace ChrisRhymes\LinkChecker\Jobs;

use ChrisRhymes\LinkChecker\Objects\Link;
use DOMDocument;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CheckModelForBrokenLinks implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private Model $model;

    private array $fields;

    private array $links = [];

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Model $model, array $fields)
    {
        $this->model = $model;
        $this->fields = $fields;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Get content fields
        collect($this->fields)
            ->map(function ($field) {
                return $this->model->{$field} ?? null;
            })
            ->filter()
            ->each(function ($field) {
                try {
                    $doc = new DOMDocument();
                    $doc->loadHTML($field);
                    $anchorTags = $doc->getElementsByTagName('a');

                    foreach ($anchorTags as $anchorTag) {
                        $link = new Link;
                        $link->url = $anchorTag->getAttribute('href');
                        $link->text = $anchorTag->nodeValue;

                        $this->links[] = $link;
                    }
                } catch (Exception $e) {
                    $className = get_class($this->model);
                    Log::info("Cannot parse HTML in {$className} model, id: {$this->model->id}.");
                }
            });

        // Check if links are broken
        foreach ($this->links as $link) {
            CheckLinkFailed::dispatch($this->model, $link);
        }
    }
}
