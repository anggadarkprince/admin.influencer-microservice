<?php

namespace App\Listeners;

use App\Events\OrderCompletedEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Message;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class NotifyAdminListener
{
    /**
     * Handle the event.
     *
     * @param OrderCompletedEvent $event
     * @return void
     */
    public function handle(OrderCompletedEvent $event)
    {
        $order = $event->order;

        Mail::send('emails.admin', ['order' => $order], function (Message $message) {
            $message->to('admin@admin.com');
            $message->subject('A new order has been completed!');
        });
    }
}
