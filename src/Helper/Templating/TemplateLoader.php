<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Helper\Templating;

use EMS\ClientHelperBundle\Helper\Environment\Environment;
use EMS\ClientHelperBundle\Helper\Environment\EnvironmentHelper;
use Twig\Loader\LoaderInterface;
use Twig\Source;

/**
 * @see EMSClientHelperExtension::defineTwigLoader()
 */
final class TemplateLoader implements LoaderInterface
{
    private EnvironmentHelper $environmentHelper;
    private TemplateBuilder $builder;

    public function __construct(EnvironmentHelper $environmentHelper, TemplateBuilder $templateBuilder)
    {
        $this->environmentHelper = $environmentHelper;
        $this->builder = $templateBuilder;
    }

    public function getSourceContext($name): Source
    {
        $environment = $this->getEnvironment();
        $templateName = new TemplateName($name);

        if ($environment->isLocalPulled()) {
            $template = $this->getEnvironment()->getLocal()->getTemplates()->getByTemplateName($templateName);

            return new Source($template->getCode(), $name, $template->getPath());
        }

        $template = $this->builder->buildTemplate($environment, $templateName);

        return new Source($template->getCode(), $name);
    }

    public function getCacheKey($name)
    {
        $environment = $this->getEnvironment();
        $key = ['twig', $environment->getAlias(), $name];

        if ($environment->isLocalPulled()) {
            $key[] = 'local';
        }

        return \implode('_', $key);
    }

    public function isFresh($name, $time)
    {
        return $this->builder->isFresh($this->getEnvironment(), new TemplateName($name), $time);
    }

    public function exists($name): bool
    {
        if (null === $this->environmentHelper->getCurrentEnvironment()) {
            return false;
        }

        return TemplateName::validate($name);
    }

    private function getEnvironment(): Environment
    {
        if (null === $environment = $this->environmentHelper->getCurrentEnvironment()) {
            throw new \RuntimeException('Can not load template without environment!');
        }

        return $environment;
    }
}
