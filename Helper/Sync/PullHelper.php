<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Helper\Sync;

use EMS\ClientHelperBundle\Helper\Translation\TranslationHelper;

final class PullHelper
{
    /** @var TranslationHelper */
    private $translationHelper;

    public function __construct(TranslationHelper $translationHelper)
    {
        $this->translationHelper = $translationHelper;
    }

    public function dumpTranslations(): void
    {
        $this->translationHelper->dump();
    }
}
