<?php

namespace Mrpath\API\Http\Middleware;

use Closure;
use Mrpath\Core\Repositories\ChannelRepository;
use Mrpath\Core\Repositories\CurrencyRepository;
use Mrpath\Core\Repositories\LocaleRepository;

class ValidateAPIHeader
{
    /**
     * Contains current guard
     *
     * @var array
     */
    protected $guard;

    /**
     * @var \Mrpath\Core\Repositories\ChannelRepository
     */
    protected $channelRepository;

    /**
     * @var \Mrpath\Core\Repositories\CurrencyRepository
     */
    protected $currencyRepository;

    /**
     * @var \Mrpath\Core\Repositories\LocaleRepository
     */
    protected $localeRepository;

    /**
     * Controller instance
     * 
     * @param \Mrpath\Core\Repositories\ChannelRepository $channelRepository
     * @param \Mrpath\Core\Repositories\CurrencyRepository $currencyRepository
     * @param \Mrpath\Core\Repositories\LocaleRepository $localeRepository
     */
    public function __construct(
        ChannelRepository $channelRepository,
        CurrencyRepository $currencyRepository,
        LocaleRepository $localeRepository
        )
    {
        $this->channelRepository = $channelRepository;

        $this->currencyRepository = $currencyRepository;

        $this->localeRepository = $localeRepository;
    }

    /**
    * Handle an incoming request.
    *
    * @param  \Illuminate\Http\Request  $request
    * @param  \Closure  $next
    * @return mixed
    */
    public function handle($request, Closure $next)
    {
        $token = request()->input('token');
        $channelId = request()->input('channel_id');

        if (! $this->validateConfigHeader())
        {
            return response()->json([
                'success'   => false,
                'message'   => trans('admin::app.api.auth.invalid-auth'),
            ], 401);
        }

        $request['token'] = $token ?: 0;
        
        // Validate the header request storeId
        if ( $channelId ) {
            $channel = $this->channelRepository->find($channelId);
            if (! $channel ) {
                return response()->json([
                    'success'   => false,
                    'message'   => trans('admin::app.api.auth.invalid-store'),
                ], 200);
            }
            
            $request['channel_id'] = $channelId;
        }
            
        return $next($request);
    }

    /**
     * Validate the config values with API header value
     *
     * @return boolean
     */
    public function validateConfigHeader()
    {
        $api_token = request()->header('api-token');
        $config_username = core()->getConfigData('general.api.settings.username');
        $config_password = core()->getConfigData('general.api.settings.password');

        if (! $api_token || 
            ! $config_username || 
            ! $config_password ||  
            ($api_token != md5($config_username . ':' . $config_password)) ) {

            return false;
        } else {

            return true;
        }
    }
}
