<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class FailureSyncNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $errorMessage;

    /**
     * Create a new message instance.
     */
    public function __construct($errorMessage)
    {
        $this->errorMessage = $errorMessage;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->from(env('MAIL_FROM_ADDRESS', 'example@example.com'), env('MAIL_FROM_NAME', 'Example'))
                    ->to(env('ADMIN_EMAIL', 'admin@example.com'))  // Adicione o destinatário aqui
                    ->subject('Falha na sincronização de produtos')
                    ->view('emails.failure-notification')
                    ->with([
                        'errorMessage' => $this->errorMessage,
                    ]);
    }
}
