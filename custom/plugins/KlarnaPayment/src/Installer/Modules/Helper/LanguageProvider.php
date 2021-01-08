<?php

declare(strict_types=1);

namespace KlarnaPayment\Installer\Modules\Helper;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\Language\LanguageEntity;

class LanguageProvider
{
    /** @var EntityRepositoryInterface */
    private $languageRepository;

    public function __construct(EntityRepositoryInterface $languageRepository)
    {
        $this->languageRepository = $languageRepository;
    }

    public function getAvailableLanguageCodes(Context $context): array
    {
        $criteria = new Criteria();
        $criteria->addAssociation('translationCode');

        $languages     = $this->languageRepository->search($criteria, $context)->getEntities();
        $languageCodes = [];

        /** @var LanguageEntity $language */
        foreach ($languages as $language) {
            if ($language->getTranslationCode() === null) {
                continue;
            }

            $languageCodes[] = $language->getTranslationCode()->getCode();
        }

        return $languageCodes;
    }
}
