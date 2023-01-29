<?php

namespace Mrpath\API\Repositories;

use Mrpath\Core\Eloquent\Repository;

class NotificationTranslationRepository extends Repository
{
    /**
     * Specify model class name.
     *
     * @return mixed
     */
    public function model()
    {
        return \Mrpath\API\Contracts\PushNotificationTranslation::class;
    }
}
