<?php

return [

    'due_date'          => 'Due Date',
    'due_at'            => 'Due Date',

    'messages' => [
        'email_sent'     => 'Receivable email has been sent successfully!',
        'marked_sent'    => 'Receivable marked as sent successfully!',
        'email_required' => 'No email address for this customer!',
        'draft'          => 'This is a <b>DRAFT</b> receivable and will be reflected to charts after it gets sent.',

        'status' => [
            'created'   => 'Created on :date',
            'send'      => [
                'draft'     => 'Not sent',
                'sent'      => 'Sent on :date',
            ],
            'paid'      => [
                'await'     => 'Awaiting payment',
            ],
        ],
    ],

    'notification' => [
        'message'       => 'You are receiving this email because you have an upcoming :amount receivable to :customer customer.',
        'button'        => 'Pay Now',
    ],

];
