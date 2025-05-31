<?php

namespace App\Notifications;

use App\Models\ZelleAddress;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderConfirmation extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    /**
     * Заказ, о котором отправляется уведомление
     */
    protected $order;

    /**
     * Create a new notification instance.
     */
    public function __construct($order)
    {
        $this->order = $order;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $order = $this->order;
        $zelleAddress = null;
        
        // Если метод оплаты - Zelle, ищем соответствующий Zelle адрес по email заказчика
        if ($order->payment_method === 'zelle') {
            $zelleAddress = ZelleAddress::where('email', $order->email)->first();
            // Не используем запасной вариант - если адрес не найден, в шаблоне покажем нейтральное сообщение
        }
        
        return (new MailMessage)
            ->subject('Спасибо за ваш заказ #' . $order->id)
            ->view('emails.orders.customer-confirmation', [
                'order' => $order,
                'zelleAddress' => $zelleAddress
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
