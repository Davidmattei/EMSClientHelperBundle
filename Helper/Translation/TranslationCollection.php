<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Helper\Translation;

use Symfony\Component\Translation\MessageCatalogueInterface;

class TranslationCollection
{
    /** @var string */
    private $locale;
    /** @var string */
    private $domain;
    /** @var array<string, string> */
    private $messages;

    /**
     * @param array<string, string> $messages
     */
    public function __construct(string $domain, string $locale, array $messages)
    {
        $this->domain = $domain;
        $this->locale = $locale;
        $this->messages = $messages;
    }

    public function filterExistingMessages(MessageCatalogueInterface $catalogue): TranslationCollection
    {
        $filtered = \array_filter($this->messages, function ($id) use ($catalogue) {
            return !$catalogue->has($id, $this->domain);
        }, \ARRAY_FILTER_USE_KEY);

        return new TranslationCollection($this->domain, $this->locale, $filtered);
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function getDomain(): string
    {
        return $this->domain;
    }

    /**
     * @return array<string, string>
     */
    public function getMessages(): array
    {
        return $this->messages;
    }
}
