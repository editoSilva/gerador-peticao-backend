<?php

namespace App\Mail;

use Log;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Support\Facades\Storage;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Contracts\Queue\ShouldQueue;

class PetitionGenerated	 extends Mailable
{
    use Queueable, SerializesModels;

    public string $pdfUrl;
    public string $fileName;
    public string $number;
    public string $description;

    /**
     * Create a new message instance.
     */
    public function __construct(public string $name, string $pdfUrl, string $fileName, string $number, string $description)
    {
        $this->pdfUrl = $pdfUrl;
        $this->fileName = $fileName;
        $this->number = $number;
        $this->description = $description;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Peticão Gerada: {$this->name}, , Nº ({$this->number})",
            
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        // Log::info('PDF URL para attachment: ' . $this->pdfUrl);

        return new Content(
            view: 'emails.petition',
            with: [
                'name' => $this->name,
                'number' => $this->number,
                'description' => $this->description
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        $pdfContent = Storage::disk('s3')->get($this->pdfUrl);

            return [
                Attachment::fromData(fn () => $pdfContent, $this->fileName)
                    ->withMime('application/pdf'),
            ];
    }
}
