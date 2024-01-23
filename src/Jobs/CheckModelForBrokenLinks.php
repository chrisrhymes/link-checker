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
use Illuminate\Support\Str;

class CheckModelForBrokenLinks implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private Model $model;

    private array $fields;

    private array $links = [];

    private ?string $base = null;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Model $model, array $fields, ?string $base = null)
    {
        $this->model = $model;
        $this->fields = $fields;
        $this->base = $base;
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
                if (Str::isUrl($field)) {
                    $link = new Link;
                    $link->url = $field;
                    $link->text = $field;

                    $this->links[] = $link;

                    return;
                }

                try {
                    $doc = new DOMDocument();
                    $doc->loadHTML($field);
                    $anchorTags = $doc->getElementsByTagName('a');

                    foreach ($anchorTags as $anchorTag) {
                        $href = $anchorTag->getAttribute('href');

                        $link = new Link;
                        $link->url = ! Str::startsWith($href, ['http://', 'https://']) && $this->base
                            ? $this->base.$href
                            : $href;
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
