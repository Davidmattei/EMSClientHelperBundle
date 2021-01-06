<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Helper\Translation;

use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;
use Symfony\Component\Translation\Dumper\YamlFileDumper;
use Symfony\Component\Translation\TranslatorBagInterface;

final class TranslationHelper implements CacheWarmerInterface
{
    /** @var TranslatorBagInterface */
    private $translator;
    /** @var TranslationBuilder */
    private $translationBuilder;

    public function __construct(TranslatorBagInterface $translator, TranslationBuilder $translationBuilder)
    {
        $this->translator = $translator;
        $this->translationBuilder = $translationBuilder;
    }

    public function dump(): void
    {
        $dumper = new YamlFileDumper('yaml');

        foreach ($this->translationBuilder->build() as $messageCatalogue) {
            $dumper->dump($messageCatalogue, ['path' => 'translations', 'as_tree' => true, 'inline' => 5]);
        }
    }

    public function load(): void
    {
        foreach ($this->translationBuilder->build() as $messageCatalogue) {
            $catalogue = $this->translator->getCatalogue($messageCatalogue->getLocale());
            $catalogue->addCatalogue($messageCatalogue);
        }
    }

    public function isOptional(): bool
    {
        return false;
    }

    public function warmUp($cacheDirectory)
    {
        try {
            $this->load();
        } catch (\Throwable $e) {
        }

        return [];
    }
}
