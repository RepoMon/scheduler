<?php namespace Ace\Scheduler\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Ace\Scheduler\Store\RDBMSStoreFactory;

/**
 * @author timrodger
 * Date: 17/07/15
 */
class StoreFactoryProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
    }

    public function boot(Application $app)
    {
        $config = $app['config'];

        $app['store-factory'] = new RDBMSStoreFactory(
            $config->getDbHost(),
            $config->getDbName(),
            $config->getDbUser(),
            $config->getDbPassword()
        );
    }
}