<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

final class WeatherRunController extends AbstractController
{
    private KernelInterface $kernel;

    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    #[Route('/weather/run', name: 'app_weather_run')]
    public function index(): Response
    {
        // Symfony komut uygulamasını hazırla
        $application = new \Symfony\Bundle\FrameworkBundle\Console\Application($this->kernel);
        $application->setAutoExit(false);

        // Çalıştırmak istediğin komutu tanımla
        $input = new ArrayInput([
            'command' => 'app:send-weather-email',
        ]);
        $output = new BufferedOutput();

        // Komutu çalıştır
        $exitCode = $application->run($input, $output);

        // Komutun çıktısını al
        $content = $output->fetch();

        // Sonucu Response olarak döndür
        return new Response(
            "Exit code: {$exitCode}\n\nOutput:\n{$content}",
            $exitCode === 0 ? 200 : 500
        );
    }
}
