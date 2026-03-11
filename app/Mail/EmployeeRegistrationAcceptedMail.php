<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EmployeeRegistrationAcceptedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public User $employee) {}

    public function build(): self
    {
        $fromAddress = (string) config('mail.from.address');
        $fromName = (string) config('mail.from.name', 'Tunisair Admin');

        return $this
            ->from($fromAddress, $fromName)
            ->subject('Votre compte est accepte - Tunisair Admin')
            ->view('emails.employee-registration-accepted', [
                'employee' => $this->employee,
            ]);
    }
}
