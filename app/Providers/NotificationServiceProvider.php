<?php

namespace App\Providers;

use App\Events\NotificationEvent;
use App\Models\Notification;
use Illuminate\Support\ServiceProvider;

class NotificationServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton('notificationService', function () {
            return new class {
                public function notifyUsers($users, $title, $message, $tableName = null, $tableId = null, $page = null )
                {
                    foreach ($users as $user) {
                        $notification = Notification::create([
                            'user_id' => $user->id,
                            'title' => $title,
                            'message' => $message,
                            'table_name' => $tableName,
                            'table_id' => $tableId,
                            'page' => $page,
                        ]);
                        // Get FCM token
                        // $userDevice = UserDevice::where('user_id', $user->id)->first();
                        // if (!$userDevice || !$userDevice->fcm_token) {
                        //     continue;
                        // }

                        // $fcmToken = $userDevice->fcm_token;

                        // // Send via Firebase
                        // $data = [
                        //     'notification_id' => (string) $notification->id,
                        //     'table_name' => $tableName,
                        //     'table_id' => (string) $tableId,
                        // ];

                        // $response = FirebaseHelper::sendNotification($fcmToken, $title, $message, $data);

                        // if (isset($response['error'])) {
                        //     Log::warning("FCM send failed for user {$user->id}: " . json_encode($response));
                        // }
                    }
                }
            };
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
