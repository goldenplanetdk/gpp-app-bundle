<?php

declare(strict_types=1);

use Doctrine\DBAL\Driver\Connection;
use GoldenPlanet\GPPAppBundle\Controller\AuthorizeController;
use GoldenPlanet\GPPAppBundle\Controller\UnAuthorizeController;
use GoldenPlanet\GPPAppBundle\EventSubscriber\HmacSubscriber;
use GoldenPlanet\GPPAppBundle\EventSubscriber\WebhookSubscriber;
use GoldenPlanet\GPPAppBundle\Security\HmacAuthenticator;
use GoldenPlanet\GPPAppBundle\Security\HmacAuthenticatorListener;
use GoldenPlanet\Gpp\App\Installer\Api\StoreApiFactory;
use GoldenPlanet\Gpp\App\Installer\AuthorizeHandler;
use GoldenPlanet\Gpp\App\Installer\Client;
use GoldenPlanet\Gpp\App\Installer\CurlHttpClient;
use GoldenPlanet\Gpp\App\Installer\Install\InstallSuccessListener;
use GoldenPlanet\Gpp\App\Installer\Uninstall\UninstallSuccessListener;
use GoldenPlanet\Gpp\App\Installer\Validator\HmacValidator;
use GoldenPlanet\Gpp\App\Installer\Validator\WebhookValidator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure()
        ->public();

    $services->set(AuthorizeController::class)
        ->private()
        ->tag('controller.service_arguments');

    $services->set(UnAuthorizeController::class)
        ->private()
        ->tag('controller.service_arguments');

    $services->set(HmacSubscriber::class)
        ->tag('kernel.event_subscriber');

    $services->set(WebhookSubscriber::class)
        ->tag('kernel.event_subscriber');

    $services->set(HmacAuthenticator::class)
        ->public();

    $services->set(CurlHttpClient::class);

    $services->alias(Client::class, CurlHttpClient::class);

    $services->set(AuthorizeHandler::class)
        ->args([
        service('event_dispatcher'),
        service(Client::class),
        '%golden_planet_gpp_app.api.app_key%',
        '%golden_planet_gpp_app.api.app_secret%',
        '%golden_planet_gpp_app.api.app_scope%',
        '%golden_planet_gpp_app.app.redirect_url%',
    ]);

    $services->set(HmacValidator::class)
        ->args([
        '%golden_planet_gpp_app.api.app_secret%',
    ]);

    $services->set(WebhookValidator::class)
        ->args([
        '%golden_planet_gpp_app.api.app_secret%',
    ]);

    $services->set(StoreApiFactory::class)
        ->args([
        service(Connection::class),
        service(Client::class),
    ]);

    $services->set(InstallSuccessListener::class)
        ->arg('$backUrl', '%golden_planet_gpp_app.app.uninstall_url%')
        ->tag('kernel.event_listener', [
        'event' => 'app.installation.success',
        'method' => 'onSuccess',
        'priority' => 1000,
    ])
        ->tag('kernel.event_listener', [
        'event' => 'app.update.scheme',
        'method' => 'updateScheme',
        'priority' => 0,
    ]);

    $services->set(UninstallSuccessListener::class)
        ->args([
        service('doctrine.dbal.default_connection'),
    ])
        ->tag('kernel.event_listener', [
        'event' => 'app.uninstalled',
        'method' => 'onSuccess',
        'priority' => -1000,
    ]);

    $services->set(HmacAuthenticatorListener::class)
        ->tag('kernel.event_listener', [
        'event' => 'kernel.request',
        'method' => 'onKernelRequest',
        'priority' => 9,
    ])
        ->arg('$firewallKey', '%firewall_key.name%');
};
