<?php
namespace App\Service;

use Symfony\Component\Notifier\ChatterInterface;
use Symfony\Component\Notifier\Message\ChatMessage;

class WhatsAppNotifier
{
    public function __construct(private ChatterInterface $chatter) {}

    public function send(string $message): void
    {
        $chat = (new ChatMessage($message))
            ->transport('whatsapp');

        $this->chatter->send($chat);
    }
}
