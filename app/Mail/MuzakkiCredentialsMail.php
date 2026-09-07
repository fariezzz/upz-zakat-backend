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

        $frontendUrl = rtrim((string) (env('FRONTEND_URL') ?: env('APP_URL', 'https://upz.unsil.ac.id')), '/');
        if (str_contains($frontendUrl, 'backend') || str_contains($frontendUrl, ':8000')) {
            $frontendUrl = 'https://upz-zakat-unsil.vercel.app';
        }
        $this->loginUrl = "{$frontendUrl}/muzakki/masuk";
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $fromAddress = config('mail.from.address') ?: env('MAIL_FROM_ADDRESS', 'noreply@upz.unsil.ac.id');
        $fromName = config('mail.from.name') ?: env('MAIL_FROM_NAME', 'UPZ Zakat UNSIL');

        return new Envelope(
            from: new Address($fromAddress, $fromName),
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
