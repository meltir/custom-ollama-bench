<?php

namespace App;

use League\Flysystem\Filesystem;

class ResponseManager
{
    public function __construct(private readonly Filesystem $filesystem)
    {}

    public function storeModelResponse(string $model, string $response, string $prompt) {
        $date = new \DateTime();
        $responsePath = $model . '/' . $prompt . '/';
        $filename = $date->format('Y-m-d_His') .  '.txt';
        $this->filesystem->write($responsePath . $filename, $response);
    }
}