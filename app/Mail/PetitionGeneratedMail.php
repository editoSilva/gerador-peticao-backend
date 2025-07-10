<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class PetitionGeneratedMail extends Mailable
{
    use Queueable, SerializesModels;

    public  $clientName;
    public $pdfPath;
    public  $attachments;

    public function __construct(string $clientName, string $pdfPath, array $attachments = [])
    {
        $this->clientName = $clientName;
        $this->pdfPath = $pdfPath;
        $this->attachments = $attachments;
    }

    public function build(): self
    {
        $mail = $this->subject('Petição Gerda com Sucesso!')
            ->view('pdf.petition')
            ->with(['name' => $this->clientName]);
//            ->attach(Storage::path($this->pdfPath), [
//                'as' => 'petition.pdf',
//                'mime' => 'application/pdf',
//            ]);

//        foreach ($this->attachments as $path) {
//            $mail->attach($path);
//        }

        return $mail;
    }
}
