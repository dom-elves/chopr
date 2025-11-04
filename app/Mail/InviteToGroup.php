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
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class InviteToGroup extends Mailable
{
    use Queueable, SerializesModels;

    public bool $is_new_user;

    /**
     * Create a new message instance.
     */
    public function __construct(public Invite $invite)
    {
        $this->is_new_user = !User::where('email', $this->invite->recipient)->exists();
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address('admin@chopr.co.uk', 'Chopr Admin'),
            replyTo:  [
                new Address('admin@chopr.co.uk', 'Chopr Admin'),
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
                'is_new_user' => $this->is_new_user,
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
