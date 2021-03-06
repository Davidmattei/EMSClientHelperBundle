<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\DependencyInjection;

use EMS\ClientHelperBundle\Helper\Api\Client as ApiClient;
use EMS\ClientHelperBundle\Helper\Elasticsearch\ClientRequest;
use EMS\ClientHelperBundle\Helper\Templating\TemplateLoader;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

final class EMSClientHelperExtension extends Extension
{
    /**
     * @param array<string, mixed> $configs
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('builders.xml');
        $loader->load('services.xml');
        $loader->load('routing.xml');
        $loader->load('search.xml');
        $loader->load('user_api.xml');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('emsch.locales', $config['locales']);
        $container->setParameter('emsch.bind_locale', $config['bind_locale'] ?? true);
        $container->setParameter('emsch.handle_exceptions', $config['handle_exceptions'] ?? true);
        $container->setParameter('emsch.etag_hash_algo', $config['etag_hash_algo'] ?? 'sha1');
        $container->setParameter('emsch.assets.enabled', $config['dump_assets']);
        $container->setParameter('emsch.request_environments', $config['request_environments']);

        $templates = $config['templates'];
        $container->getDefinition('emsch.helper_exception')->replaceArgument(4, $templates['error']);
        $container->getDefinition('emsch.routing.url.transformer')->replaceArgument(4, $templates['ems_link']);

        $this->processElasticms($container, $loader, $config['elasticms']);
        $this->processApi($container, $config['api']);
        $this->processUserApi($container, $config['user_api']);

        if ($config['local']) {
            $loader->load('local.xml');
        }
    }

    /**
     * @param array<string, mixed> $config
     */
    private function processElasticms(ContainerBuilder $container, XmlFileLoader $loader, array $config): void
    {
        foreach ($config as $name => $options) {
            $this->defineClientRequest($container, $loader, $name, $options);

            if (isset($options['templates'])) {
                $this->defineTwigLoader($container, $name);
            }
        }
    }

    /**
     * @param array<string, mixed> $config
     */
    private function processApi(ContainerBuilder $container, array $config): void
    {
        foreach ($config as $name => $options) {
            $definition = new Definition(ApiClient::class);
            $definition->setArgument(0, $name);
            $definition->setArgument(1, $options['url']);
            $definition->setArgument(2, $options['key']);
            $definition->setArgument(3, new Reference('logger'));
            $definition->addTag('emsch.api_client');

            $container->setDefinition(\sprintf('emsch.api_client.%s', $name), $definition);
        }
    }

    /**
     * @param array<string, mixed> $options
     */
    private function defineClientRequest(ContainerBuilder $container, XmlFileLoader $loader, string $name, array $options): void
    {
        $definition = new Definition(ClientRequest::class);
        $definition->setArguments([
            new Reference('ems_common.service.elastica'),
            new Reference('emsch.helper_environment'),
            new Reference('emsch.helper_cache'),
            new Reference('emsch.helper_content_type'),
            new Reference('logger'),
            new Reference('Psr\Cache\CacheItemPoolInterface'),
            $name,
            $options,
        ]);
        $definition->addTag('emsch.client_request');

        if (isset($options['api'])) {
            $this->loadClientRequestApi($loader);
            $definition->addTag('emsch.client_request.api');
        }

        $container->setDefinition(\sprintf('emsch.client_request.%s', $name), $definition);
    }

    private function loadClientRequestApi(XmlFileLoader $loader): void
    {
        static $loaded = false;

        if ($loaded) {
            return;
        }

        $loader->load('api.xml');
        $loaded = true;
    }

    private function defineTwigLoader(ContainerBuilder $container, string $name): void
    {
        $loader = new Definition(TemplateLoader::class);
        $loader->setArguments([
            new Reference('emsch.helper_environment'),
            new Reference('emsch.helper.templating.builder'),
        ]);
        $loader->addTag('twig.loader', ['alias' => $name, 'priority' => 1]);

        $container->setDefinition(\sprintf('emsch.twig.loader.%s', $name), $loader);
    }

    /**
     * @param array<string, mixed> $config
     */
    private function processUserApi(ContainerBuilder $container, array $config): void
    {
        if (!$config['enabled']) {
            return;
        }

        $container->setParameter('emsch.user_api.url', $config['url']);
    }
}
