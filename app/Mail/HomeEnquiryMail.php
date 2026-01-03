<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class HomeEnquiryMail extends Mailable
{
    use Queueable, SerializesModels;
    public array $enquiry;
    public function __construct(array $enquiry)
    {
        $this->enquiry = $enquiry;
    }

    public function build()
    {
        return $this->subject('New Enquiry Received')
            ->view('backend.mail.home-enquiry')
            ->with([
                'enquiry' => $this->enquiry
            ]);
    }
}
