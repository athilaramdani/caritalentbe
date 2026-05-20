<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'body',
        'type',
        'action',
        'reference_type',
        'reference_id',
        'data',
        'is_read',
        'read_at',
    ];

    protected $casts = [
        'data' => 'array',
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    protected static function booted()
    {
        static::created(function ($notification) {
            // Load user to get fcm_token
            $user = $notification->user;
            
            if ($user && $user->fcm_token) {
                try {
                    $messaging = app('firebase.messaging');
                    
                    $message = \Kreait\Firebase\Messaging\CloudMessage::withTarget('token', $user->fcm_token)
                        ->withNotification(\Kreait\Firebase\Messaging\Notification::create(
                            $notification->title,
                            $notification->body
                        ))
                        ->withData([
                            'id' => (string) $notification->id,
                            'type' => (string) $notification->type,
                            'action' => (string) $notification->action,
                            'reference_id' => (string) $notification->reference_id,
                        ]);
                        
                    $messaging->send($message);
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('FCM Send Error: ' . $e->getMessage());
                }
            }
        });
    }
}
