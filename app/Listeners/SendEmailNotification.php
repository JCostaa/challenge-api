<?php

namespace App\Listeners;

use App\Events\SendFailureSyncNotification;  // Mude o import
use App\Mail\FailureSyncNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class SendEmailNotification implements ShouldQueue
{
    public function handle(SendFailureSyncNotification $event)  // Mude o tipo aqui
    {
        Mail::to(env('ADMIN_EMAIL', 'admin@example.com'))
            ->send(new FailureSyncNotification($event->errorMessage));
    }
}
