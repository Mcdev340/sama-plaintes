<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MessageContact extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $nom,
        public string $emailExpediteur,
        public string $sujetMessage,
        public string $corps,
        public ?string $localisation = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            replyTo: [new Address($this->emailExpediteur, $this->nom)],
            subject: 'Contact Sama Plaintes — '.$this->sujetMessage,
        );
    }

    public function content(): Content
    {
        return new Content(text: 'mail.contact');
    }
}
