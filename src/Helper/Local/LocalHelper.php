<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Helper\Local;

use EMS\ClientHelperBundle\Helper\Environment\Environment;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

final class LocalHelper
{
    private Filesystem $filesystem;
    private string $projectDir;

    public const TYPE_ROUTES = 'routes';
    public const TYPE_TRANSLATIONS = 'translations';
    public const TYPE_TEMPLATES = 'templates';
    private const WORKING_DIR = 'local';

    public function __construct(string $projectDir)
    {
        $this->projectDir = $projectDir;
        $this->filesystem = new Filesystem();
    }

    /**
     * @return TranslationFile[]
     */
    public function getTranslationFiles(Environment $environment): array
    {
        $files = [];
        $dirTranslations = $this->getDirTranslations($environment);

        foreach (Finder::create()->in($dirTranslations)->files()->name('*.yaml') as $file) {
            $files[] = new TranslationFile($file);
        }

        return $files;
    }

    public function getDirTranslations(Environment $environment): string
    {
        return $this->getFilePath($environment, ['translations']);
    }

    public function getDirTemplates(Environment $environment): string
    {
        return $this->getFilePath($environment, ['templates']);
    }

    public function getFileTemplates(Environment $environment): string
    {
        return $this->getFilePath($environment, ['templates.json']);
    }

    public function getFileRoutes(Environment $environment): string
    {
        return $this->getFilePath($environment, ['routes.json']);
    }

    /**
     * @param string[] $append
     */
    public function getFilePath(Environment $environment, array $append = []): string
    {
        $path = \array_filter([$this->projectDir, self::WORKING_DIR, $environment->getAlias()]);

        return \implode(DIRECTORY_SEPARATOR, \array_merge($path, $append));
    }
}
