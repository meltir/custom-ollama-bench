#!/usr/bin/env php
<?php

require __DIR__ . '/vendor/autoload.php';

use App\OllamaBatchCommand;
use App\OllamaBatch;
use Symfony\Component\Console\Application;
use Symfony\Component\Dotenv\Dotenv;

$dotenv = new Dotenv();
$dotenv->loadEnv(__DIR__.'/.env');


$endpoint = $_ENV('OLLAMA_ENDPOINT') ?: 'http://localhost:11434';
$ollamaBatch = new OllamaBatch($endpoint);

$application = new Application();
$application->add(new OllamaBatchCommand($ollamaBatch));

$application->run();
