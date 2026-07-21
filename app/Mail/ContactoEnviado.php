<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactoEnviado extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $nombreRemitente,
        public string $emailRemitente,
        public string $asunto,
        public string $descripcion,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Contacto Aliste.info: '.$this->asunto,
            replyTo: [new Address($this->emailRemitente, $this->nombreRemitente)],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.contacto',
        );
    }
}
