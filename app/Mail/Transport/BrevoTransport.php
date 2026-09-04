<?php

namespace App\Mail\Transport;

use Illuminate\Support\Facades\Http;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\MessageConverter;

class BrevoTransport extends AbstractTransport
{
    protected string $apiKey;

    public function __construct(string $apiKey)
    {
        parent::__construct();
        $this->apiKey = $apiKey;
    }

    protected function doSend(SentMessage $message): void
    {
        $email = MessageConverter::toEmail($message->getOriginalMessage());

        $from = $email->getFrom()[0] ?? new Address(config('mail.from.address'), config('mail.from.name'));
        $toAddresses = array_map(function (Address $addr) {
            $data = ['email' => $addr->getAddress()];
            if ($name = $addr->getName()) {
                $data['name'] = $name;
            }
            return $data;
        }, $email->getTo());

        $payload = [
            'sender' => [
                'name' => $from->getName() ?: (config('mail.from.name') ?: 'Smart Car Service'),
                'email' => $from->getAddress() ?: config('mail.from.address'),
            ],
            'to' => array_values($toAddresses),
            'subject' => $email->getSubject() ?: 'Notification',
            'htmlContent' => $email->getHtmlBody() ?: nl2br($email->getTextBody() ?? ''),
        ];

        $response = Http::withHeaders([
            'api-key' => $this->apiKey,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ])->post('https://api.brevo.com/v3/smtp/email', $payload);

        if (!$response->successful()) {
            throw new \RuntimeException('Brevo API email send failed: ' . $response->body());
        }
    }

    public function __toString(): string
    {
        return 'brevo';
    }
}
