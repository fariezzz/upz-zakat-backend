<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Address;

class MuzakkiCredentialsMail extends Mailable
{
    use Queueable, SerializesModels;

    public $nama;
    public $email;
    public $password;
    public $loginUrl;

    /**
     * Create a new message instance.
     */
    public function __construct($nama, $email, $password)
    {
        $this->nama = $nama;
        $this->email = $email;
        $this->password = $password;
        $this->loginUrl = 'https://upz.unsil.ac.id/masuk-muzakki';
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address('noreply@upz.unsil.ac.id', 'UPZ Zakat UNSIL'),
            subject: 'Akun Muzakki UPZ Zakat UNSIL Anda',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.muzakki-credentials',
            text: 'emails.muzakki-credentials-text',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
