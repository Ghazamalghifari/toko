<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\SlackMessage;
use App\Bank;

class PembayaranTopos extends Notification
{
    use Queueable;

    /**
    * Create a new notification instance.
    *
    * @return void
    */
    public function __construct($pendaftar_topos)
    {
        //
        $this->pendaftar_topos = $pendaftar_topos;
    }

    /**
    * Get the notification's delivery channels.
    *
    * @param  mixed  $notifiable
    * @return array
    */
    public function via($notifiable)
    {
        return ['slack'];
    }

    /**
    * Get the mail representation of the notification.
    *
    * @param  mixed  $notifiable
    * @return \Illuminate\Notifications\Messages\MailMessage
    */
    public function toSlack($notifiable)
    { 
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
     'title' => 'testing',
     'description' => 'test'
 ];
}
}

?>
