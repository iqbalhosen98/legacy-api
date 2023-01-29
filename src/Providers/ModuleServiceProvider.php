<?php

namespace Mrpath\API\Providers;

use Mrpath\Core\Providers\CoreModuleServiceProvider;

class ModuleServiceProvider extends CoreModuleServiceProvider
{
    protected $models = [
        \Mrpath\API\Models\PushNotification::class,
        \Mrpath\API\Models\PushNotificationTranslation::class,
    ];
}
