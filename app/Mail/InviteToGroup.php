<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Queue\SerializesModels;
use App\Models\Invite;
use Illuminate\Support\Facades\Auth;

class InviteToGroup extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(public Invite $invite)
    {

    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address('hello@example.com', 'Chopr Admin'),
            replyTo:  [
                new Address('hello@example.com', 'Chopr Admin'),
            ],
            subject: 'Invite To Group',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
    
        return new Content(
            view: 'emails.invite-to-group',
            with: [
                // this is for anything not accessible via $invite
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
        return [];
    }
}
