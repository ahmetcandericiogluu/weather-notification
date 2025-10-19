<?php

use App\Kernel;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\ErrorHandler\Debug;
use Symfony\Component\HttpFoundation\Request;

require dirname(__DIR__).'/vendor/autoload.php';

// Eğer .env dosyası varsa (local ortamda) yükle, yoksa hatayı yoksay
$envPath = dirname(__DIR__).'/.env';
if (file_exists($envPath)) {
    (new Dotenv())->bootEnv($envPath);
}

$kernel = new Kernel($_SERVER['APP_ENV'] ?? 'prod', (bool) ($_SERVER['APP_DEBUG'] ?? false));
$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
