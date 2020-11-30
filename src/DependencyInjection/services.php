<?php declare(strict_types=1);

use DI\Container;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Riotkit\UptimeAdminBoard\Component\Config;
use Riotkit\UptimeAdminBoard\Factory\UrlFactory;
use Riotkit\UptimeAdminBoard\Persistence\InfluxDBPersistence;
use Riotkit\UptimeAdminBoard\Persistence\PersistenceInterface;
use Riotkit\UptimeAdminBoard\Provider\InfracheckProvider;
use Riotkit\UptimeAdminBoard\Provider\MultipleProvider;
use Riotkit\UptimeAdminBoard\Provider\ServerUptimeProviderInterface;
use Riotkit\UptimeAdminBoard\Provider\UptimeRobotProvider;

return [

    //
    // Infrastructure
    //
    
    LoggerInterface::class => static function (Config $config) {
        $logger = new Logger('riothealthflux');

        if (PHP_SAPI === 'cli') {
            $logger->pushHandler(new StreamHandler('php://stdout', Logger::INFO));
        }

        if ($config->get('log_path', '')) {
            $logger->pushHandler(new StreamHandler($config->get('log_path'), Logger::INFO));
        }

        return $logger;
    },

    PersistenceInterface::class => static function (Container $container) {
        return $container->get(InfluxDBPersistence::class);
    },

    InfluxDBPersistence::class => static function (Config $config) {
        return new InfluxDBPersistence(
            $config->get('influxdb_url'),
            $config->get('influxdb_token'),
            $config->get('influxdb_bucket'),
            $config->get('influxdb_org')
        );
    },


    //
    // Application
    //

    // STARTS: Provider chain
    ServerUptimeProviderInterface::class => static function (Container $container) {
        return $container->get(MultipleProvider::class);
    },

    MultipleProvider::class => static function (Container $container) {
        return new MultipleProvider(
            [
                $container->get(InfracheckProvider::class),
                $container->get(UptimeRobotProvider::class),
            ],
            $container->get(LoggerInterface::class)
        );
    },

    UptimeRobotProvider::class => static function () {
        return new UptimeRobotProvider();
    },

    // ENDS: Provider chain

    UrlFactory::class => static function (Config $config) {
        return new UrlFactory($config->get('providers'));
    },

    Config::class => static function () {
        return new Config(require __DIR__ . '/../../config.php');
    }

    // ... the rest of services are not configured due to their simple dependencies
    //     that are resolved automatically by autowiring in the DI container
];
