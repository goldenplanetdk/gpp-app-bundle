<?php

namespace GoldenPlanet\GPPAppBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @link http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class GoldenPlanetGPPAppExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');

        $container->setParameter('gpp_app.api.app_key', $config['api']['app_key']);
        $container->setParameter('gpp_app.api.app_secret', $config['api']['app_secret']);
        $container->setParameter('gpp_app.api.app_scope', $config['api']['app_scope']);
        $container->setParameter('gpp_app.app.redirect_url', $config['app']['redirect_url']);
        $container->setParameter('gpp_app.app.uninstall_url', $config['app']['uninstall_url']);
    }
}
