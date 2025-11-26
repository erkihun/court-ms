<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ApplicantReceiptMail extends Mailable
{
    use Queueable, SerializesModels;

    public $case;
    protected $pdfBinary;

    public function __construct($case, string $pdfBinary)
    {
        $this->case = $case;
        $this->pdfBinary = $pdfBinary;
    }

    public function build()
    {
        $filename = 'receipt-' . ($this->case->case_number ?? 'case') . '.pdf';

        return $this->subject('Your filing receipt: ' . ($this->case->case_number ?? ''))
            ->view('mail.applicant_receipt')
            ->attachData($this->pdfBinary, $filename, ['mime' => 'application/pdf']);
    }
}
