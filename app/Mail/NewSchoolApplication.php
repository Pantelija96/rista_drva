<?php

namespace App\Mail;

use App\Models\SchoolApplication;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class NewSchoolApplication extends Mailable
{
    public function __construct(public SchoolApplication $application) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'EcoSync — nova prijava škole');
    }

    public function content(): Content
    {
        return new Content(view: 'mail.school-application');
    }
}
