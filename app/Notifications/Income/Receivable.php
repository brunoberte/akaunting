<?php

namespace App\Notifications\Income;

use App\Util;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class Receivable extends Notification
{
    /**
     * The bill model.
     *
     * @var object
     */
    public $receivable;

    /**
     * Create a notification instance.
     *
     * @param  object  $receivable
     */
    public function __construct($receivable)
    {
        $this->queue = 'high';
        $this->delay = config('queue.connections.database.delay');

        $this->receivable = $receivable;
    }

    /**
     * Get the notification's channels.
     *
     * @param  mixed  $notifiable
     * @return array|string
     */
    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    /**
     * Build the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $message = (new MailMessage)
            ->line(trans('invoices.notification.message', ['amount' => Util::money($this->receivable->amount, $this->receivable->currency_code), 'customer' => $this->receivable->customer_name]));

        // Override per company as Laravel doesn't read config
        $message->from(config('mail.from.address'), config('mail.from.name'));

        // Attach the PDF file if available
        if (isset($this->receivable->pdf_path)) {
            $message->attach($this->receivable->pdf_path, [
                'mime' => 'application/pdf',
            ]);
        }

        if ($this->receivable->customer->user) {
            $message->action(trans('invoices.notification.button'), url('customers/invoices', $this->receivable->id, true));
        }

        return $message;
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'invoice_id' => $this->receivable->id,
            'amount' => $this->receivable->amount,
        ];
    }
}
