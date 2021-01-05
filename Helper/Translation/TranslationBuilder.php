<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Helper\Translation;

use EMS\ClientHelperBundle\Helper\Cache\CacheHelper;
use EMS\ClientHelperBundle\Helper\Elasticsearch\ClientRequest;
use EMS\ClientHelperBundle\Helper\Elasticsearch\ClientRequestManager;

final class TranslationBuilder
{
    /** @var CacheHelper */
    private $cache;
    /** @var string[] */
    private $locales;
    /** @var ClientRequestManager */
    private $manager;

    /**
     * @param string[] $locales
     */
    public function __construct(ClientRequestManager $manager, CacheHelper $cache, array $locales)
    {
        $this->cache = $cache;
        $this->locales = $locales;
        $this->manager = $manager;
        $this->locales = $locales;
    }

    /**
     * @return string[]
     */
    public function getLocales(): array
    {
        return $this->locales;
    }

    /**
     * @return \Generator|TranslationCollection[]
     */
    public function build(): \Generator
    {
        foreach ($this->manager->all() as $clientRequest) {
            if (!$clientRequest->hasOption('translation_type')) {
                continue;
            }

            if (!$clientRequest->mustBeBind() && !$clientRequest->hasEnvironments()) {
                continue;
            }

            $translationDomain = $clientRequest->getCacheKey();

            foreach ($this->getMessages($clientRequest) as $locale => $messages) {
                yield new TranslationCollection($translationDomain, $locale, $messages);
            }
        }
    }

    /**
     * @return array<string, array<string, string>>
     */
    private function getMessages(ClientRequest $clientRequest): array
    {
        $cacheItem = $this->cache->get($clientRequest->getCacheKey('translations'));
        $lastChanged = $clientRequest->getLastChangeDate($this->getTranslationContentType($clientRequest));

        if ($cacheItem && $this->cache->isValid($cacheItem, $lastChanged)) {
            $messages = $this->cache->getData($cacheItem);
        } else {
            $messages = $this->createMessages($clientRequest);

            if ($cacheItem) {
                $this->cache->save($cacheItem, $messages);
            }
        }

        return $messages;
    }

    private function getTranslationContentType(ClientRequest $clientRequest): string
    {
        $contentType = $clientRequest->getOption('[translation_type]', null);

        if (null === $contentType || !\is_string($contentType)) {
            throw new \RuntimeException('Call hasOption [translation_type] first!');
        }

        return $contentType;
    }

    /**
     * @return array<string, array<string, string>>
     */
    private function createMessages(ClientRequest $clientRequest): array
    {
        $messages = [];

        $scroll = $clientRequest->scrollAll([
            'size' => 100,
            'type' => $this->getTranslationContentType($clientRequest),
        ], '5s');

        foreach ($scroll as $hit) {
            foreach ($this->locales as $locale) {
                if (isset($hit['_source']['label_'.$locale])) {
                    $messages[$locale][(string) $hit['_source']['key']] = (string) $hit['_source']['label_'.$locale];
                }
            }
        }

        return $messages;
    }
}
