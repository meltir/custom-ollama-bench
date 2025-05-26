<?php

namespace App;

use League\Flysystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;
use Ollama\Ollama;
use Ollama\Responses\Models\ListModelsResponse;

/**
 * @todo add console command and take in params
 * @todo add lock file and pick up if process crashes
 * @todo make prompt an object, with target filename
 * @todo extract suggested code contents and test contents
 * @todo execute sanity checks on code
 * @todo if it runs and is valid, run tests in docker
 * @todo if tests pass, run infection against code via docker
 * @todo define ideal test file in prompt object
 * @todo move prompts to their own folders, define a json schema for them
 * @todo support other languages ?
 * @todo move to docker container
 * @todo create a nice report
 * @todo report on distance between ideal solution and proposed solutions
 * @todo detect delirium (nonsensical output full of hallucinations)
 * @todo include results in github repo
 * @todo add gitignore file
 */
class OllamaBatch
{
    private Ollama $client;
    private Filesystem $filesystem;
    private ResponseManager $responseManager;

    public function __construct(private readonly string $endpoint)
    {
        $this->client = Ollama::client($this->endpoint);
        $fsAdapter = new LocalFilesystemAdapter(__DIR__ . '/../results');
        $this->filesystem = new Filesystem($fsAdapter);
        $this->responseManager = new ResponseManager($this->filesystem);
    }

    public function getAvailableModels(): ListModelsResponse
    {
        return $this->client->models()->list();
    }

    public function getModelsSmallerThan(int $min, int $max): array
    {
        $gbMultiplier = 1024*1024*1024;
        $qualifyingModels = [];
        $minBytes = $min*$gbMultiplier;
        $maxBytes = $max*$gbMultiplier;
        foreach ($this->getAvailableModels()->models as $model) {
            if ($model->size < $maxBytes && $model->size > $minBytes) {
                $qualifyingModels[] = $model;
            }
        }
        return $qualifyingModels;
    }

    public function runBatch(string $prompt, int $minModelSize = 0, int $maxModelSize = 1)
    {
        $models = $this->getModelsSmallerThan($minModelSize, $maxModelSize);
        echo count($models) . " models in batch\n";
        foreach ($models as $model) {
//            echo "MODEL: " . $model->name . ' - ' . $model->size . "\n";
            $this->complete($prompt, $model->name);
        }
    }

    public function complete(string $prompt, string $model): void
    {
        echo "CALLING MODEL: $model\n";
        $completion = $this->client->completions()->create([
           'model' => $model,
           'prompt' => $prompt,
           'keep_alive' => '1s'
        ]);
        $this->responseManager->storeModelResponse($model, $completion->response, 'SequenceGenerator');
        echo "CALL COMPLETED\n";
//        var_dump($completion->response);
    }
}