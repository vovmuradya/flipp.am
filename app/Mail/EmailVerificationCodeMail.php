<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EmailVerificationCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(private readonly string $code)
    {
    }

    public function build(): self
    {
        return $this
            ->subject(__('Код подтверждения email'))
            ->view('emails.verification_code', [
                'code' => $this->code,
            ]);
    }
}
