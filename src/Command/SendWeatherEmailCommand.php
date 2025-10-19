<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;


#[AsCommand(
    name: 'app:send-weather-email',
    description: 'İstanbul hava durumunu mail olarak gönderir.'
)]
class SendWeatherEmailCommand extends Command
{

    private $client;
    private $mailer;
    private $apiKey;
    private $params;

    public function __construct(HttpClientInterface $client, MailerInterface $mailer, string $apiKey, ParameterBagInterface $params)
    {
        parent::__construct();
        $this->client = $client;
        $this->mailer = $mailer;
        $this->apiKey = $apiKey;
        $this->params = $params;
    }

    protected function configure()
    {
        $this->setDescription('İstanbul hava durumunu mail olarak gönderir.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // 1. OpenWeather API isteği
        $response = $this->client->request('GET', 'https://api.openweathermap.org/data/2.5/forecast', [
            'query' => [
                'q' => $this->params->get('openweather.city') ?? 'Istanbul',
                'appid' => $this->apiKey,
                'units' => $this->params->get('openweather.units') ?? 'metric',
                'lang' => 'tr',
            ],
        ]);

        $data = $response->toArray();
        $today = (new \DateTime('now', new \DateTimeZone('Europe/Istanbul')))->format('Y-m-d');

        $todayTemps = [];
        foreach ($data['list'] as $item) {
            $dt = (new \DateTime('@' . $item['dt']))->setTimezone(new \DateTimeZone('Europe/Istanbul'));
            if ($dt->format('Y-m-d') === $today) {
                $todayTemps[] = $item['main'];
            }
        }

        if (empty($todayTemps)) {
            $output->writeln('Bugün için tahmin verisi bulunamadı.');
            return Command::SUCCESS;
        }

         // Günlük en düşük ve en yüksek tahminleri bul
        $minTemp = min(array_column($todayTemps, 'temp_min'));
        $maxTemp = max(array_column($todayTemps, 'temp_max'));

        $desc = ucfirst($data['list'][0]['weather'][0]['description']);

        $content = <<<MAIL
    🌤️ İstanbul Günlük Hava Tahmini

    Durum: {$desc}
    Bugün en düşük: {$minTemp}°C
    Bugün en yüksek: {$maxTemp}°C

    Kaynak: OpenWeatherMap
    MAIL;
        

        // 2. Mail gönder
        $email = (new Email())
            ->from($this->params->get('mail.from'))
            ->to($this->params->get('mail.to'))
            ->subject('İstanbul Hava Durumu - ' . date('d.m.Y'))
            ->text($content);

        $this->mailer->send($email);

        $output->writeln('✅ Hava durumu e-postası gönderildi.');
        return Command::SUCCESS;
    }
}
