<?php

namespace App\Mail;

use App\Models\ProcessedPhoto;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SharePhotosMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The ProcessedPhoto instance.
     */
    public $photo;

    /**
     * Create a new message instance.
     */
    public function __construct(ProcessedPhoto $photo)
    {
        $this->photo = $photo;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Foto Bareng Hanari!',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.share_photos',
        );
    }

    public function attachments(): array
    {
        $attachments = [];
        $isPolaroid = str_ends_with($this->photo->raw_path, '.zip');

        // Only attach the framed collage image or framed single photo to keep the email size small
        $attachments[] = Attachment::fromStorageDisk('public', $this->photo->framed_path)
            ->as($isPolaroid ? 'hanari_polaroid_collage.png' : 'foto_dengan_bingkai.png')
            ->withMime('image/png');

        return $attachments;
    }
}
