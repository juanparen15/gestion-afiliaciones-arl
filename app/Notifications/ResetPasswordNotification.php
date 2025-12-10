<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification
{
    use Queueable;

    /**
     * El token de restablecimiento de contraseña.
     *
     * @var string
     */
    public $token;

    /**
     * La URL de callback.
     *
     * @var string|null
     */
    public static $createUrlCallback;

    /**
     * Crear una nueva instancia de notificación.
     */
    public function __construct($token)
    {
        $this->token = $token;
    }

    /**
     * Obtener los canales de entrega de la notificación.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Obtener la representación de correo de la notificación.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $url = $this->resetUrl($notifiable);

        return (new MailMessage)
            ->subject('Notificación de Restablecimiento de Contraseña')
            ->greeting('¡Hola!')
            ->line('Estás recibiendo este correo porque hemos recibido una solicitud de restablecimiento de contraseña para tu cuenta.')
            ->action('Restablecer Contraseña', $url)
            ->line('Este enlace de restablecimiento de contraseña expirará en ' . config('auth.passwords.'.config('auth.defaults.passwords').'.expire') . ' minutos.')
            ->line('Si no solicitaste restablecer tu contraseña, no es necesario realizar ninguna otra acción.')
            ->salutation('Saludos, ' . config('app.name'));
    }

    /**
     * Obtener la URL de restablecimiento de contraseña.
     *
     * @param  mixed  $notifiable
     * @return string
     */
    protected function resetUrl($notifiable)
    {
        if (static::$createUrlCallback) {
            return call_user_func(static::$createUrlCallback, $notifiable, $this->token);
        }

        return url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));
    }

    /**
     * Establecer una callback para generar la URL de restablecimiento.
     *
     * @param  \Closure  $callback
     * @return void
     */
    public static function createUrlUsing($callback)
    {
        static::$createUrlCallback = $callback;
    }

    /**
     * Obtener la representación de array de la notificación.
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
