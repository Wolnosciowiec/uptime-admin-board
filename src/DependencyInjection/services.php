<?php declare(strict_types=1);

use DI\Container;
use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\FilesystemCache;
use Doctrine\Common\Cache\RedisCache;
use Riotkit\UptimeAdminBoard\ActionHandler\ShowServicesAvailabilityAction;
use Riotkit\UptimeAdminBoard\Component\Config;
use Riotkit\UptimeAdminBoard\Controller\DashboardController;
use Riotkit\UptimeAdminBoard\Provider\MultipleProvider;
use Riotkit\UptimeAdminBoard\Provider\ServerUptimeProvider;
use Riotkit\UptimeAdminBoard\Provider\UptimeRobotProvider;
use Riotkit\UptimeAdminBoard\Provider\WithMetricsRecordedProvider;
use Riotkit\UptimeAdminBoard\Provider\WithTorWrapperProvider;
use Riotkit\UptimeAdminBoard\Repository\HistoryRepository;
use Riotkit\UptimeAdminBoard\Repository\HistorySQLiteRepository;
use Riotkit\UptimeAdminBoard\Service\TORProxyHandler;
use Twig\Environment;

return [

    //
    // Infrastructure
    //
    
    Cache::class => function (Container $container) {
        $config    = $container->get(Config::class);
        $cacheType = $config->get('cache');

        if ($cacheType === 'redis') {
            $redis = new Redis();
            $redis->connect($config->get('redis_host'), $config->get('redis_port'));

            $cache = new RedisCache();
            $cache->setRedis($redis);

            return $cache;
        }

        return new FilesystemCache(__DIR__ . '/../../var/cache/');
    },

    Twig\Environment::class => function (Container $container) {
        return new Twig\Environment(
            new Twig\Loader\FilesystemLoader([__DIR__ . '/../../templates/'])
        );
    },


    //
    // Application
    //

    DashboardController::class => function (Container $container, Config $config) {
        return new DashboardController(
            $container->get(ShowServicesAvailabilityAction::class),
            $container->get(Environment::class),
            $config->get('dynamic_dashboard') ? 'dashboard-dynamic.html.twig' : 'dashboard.html.twig'
        );
    },

    HistorySQLiteRepository::class => function (Config $config) {
        return new HistorySQLiteRepository(
            $config->get('db_path')
        );
    },

    HistoryRepository::class => static function (Container $container) {
        return $container->get(HistorySQLiteRepository::class);
    },

    // STARTS: Provider chain
    ServerUptimeProvider::class => static function (Container $container) {
        return $container->get(WithMetricsRecordedProvider::class);
    },

    WithTorWrapperProvider::class => function (Container $container) {
        return new WithTorWrapperProvider(
            $container->get(MultipleProvider::class),
            $container->get(TORProxyHandler::class)
        );
    },

    WithMetricsRecordedProvider::class => function (Container $container, Config $config) {
        return new WithMetricsRecordedProvider(
            $container->get(WithTorWrapperProvider::class),
            $container->get(HistoryRepository::class),
            $config->get('history_max_days')
        );
    },

    MultipleProvider::class => function (Container $container) {
        return new MultipleProvider([
            $container->get(UptimeRobotProvider::class)
        ]);
    },
    // ENDS: Provider chain

    TORProxyHandler::class => function (Container $container) {
        $config = $container->get(Config::class);

        return new TORProxyHandler(
            $config->get('proxy_address', ''),
            $config->get('tor_management_port', 9052),
            $config->get('tor_password', '')
        );
    },

    Config::class => function () {
        return new Config(require __DIR__ . '/../../config.php');
    }

    // ... the rest of services are not configured due to their simple dependencies
    //     that are resolved automatically by autowiring in the DI container
];
