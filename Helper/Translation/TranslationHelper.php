<?php

namespace EMS\ClientHelperBundle\Helper\Translation;

use Symfony\Component\Translation\Dumper\YamlFileDumper;
use Symfony\Component\Translation\MessageCatalogue;
use Symfony\Component\Translation\TranslatorBagInterface;

class TranslationHelper
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

        foreach ($this->translationBuilder->build() as $collection) {
            $catalogue = new MessageCatalogue($collection->getLocale());
            $catalogue->add($collection->getMessages(), $collection->getDomain());

            $dumper->dump($catalogue, ['path' => 'translations', 'as_tree' => true]);
        }
    }

    public function load(): void
    {
        foreach ($this->translationBuilder->build() as $collection) {
            $catalogue = $this->translator->getCatalogue($collection->getLocale());
            $filteredCollection = $collection->filterExistingMessages($catalogue);

            $catalogue->add($filteredCollection->getMessages(), $filteredCollection->getDomain());
        }
    }

    public function isOptional(): bool
    {
        return false;
    }

    public function warmUp(): void
    {
        try {
            $this->load();
        } catch (\Throwable $e) {
        }
    }
}
