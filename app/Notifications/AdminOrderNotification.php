<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminOrderNotification extends Notification
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
     * Адрес электронной почты администратора
     */
    protected $adminEmail;

    /**
     * Расчет корзины с купонами
     */
    protected $calculation;

    /**
     * Примененные купоны
     */
    protected $appliedCoupons;

    /**
     * Create a new notification instance.
     */
    public function __construct($order, $adminEmail, $calculation = null, $appliedCoupons = [])
    {
        $this->order = $order;
        $this->adminEmail = $adminEmail;
        $this->calculation = $calculation;
        $this->appliedCoupons = $appliedCoupons;
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
        return (new MailMessage)
            ->subject('New Order #' . $this->order->id . ' - CruseStick Admin Alert')
            ->view('emails.orders.admin-notification', [
                'order' => $this->order,
                'calculation' => $this->calculation,
                'appliedCoupons' => $this->appliedCoupons,
                'recipientEmail' => $this->adminEmail
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
