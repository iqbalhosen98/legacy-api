<?php

namespace Mrpath\API\Models;

use Illuminate\Database\Eloquent\Model;
use Mrpath\API\Contracts\PushNotificationTranslation as PushNotificationTranslationContract;

/**
 * Class NotificationTranslation
 *
 * @package Mrpath\API\Models
 *
 */
class PushNotificationTranslation extends Model implements PushNotificationTranslationContract
{
    public $timestamps = false;
    
    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    /**
     * Get the notification that owns the attribute value.
     */
    public function notification()
    {
        return $this->belongsTo(PushNotificationProxy::modelClass());
    }
}
