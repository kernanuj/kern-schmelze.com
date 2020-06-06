<?php declare(strict_types=1);

namespace shopueberDeutschDu;

use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\MultiFilter;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\InstallContext;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Shopware\Core\Framework\Uuid\Uuid;
use shopueberDeutschDu\Resources\app\core\snippet\LanguagePack as SnippetFile;

class shopueberDeutschDu extends Plugin
{
    public const SHOPUEBER_LOCALE_CODE = 'de-DE';
    public const SHOPUEBER_LANGUAGE_NAME = 'Deutsch DU-Version';

    public function install(InstallContext $context): void
    {
        $this->addLanguage($context->getContext());
        $this->addBaseSnippetSet($context->getContext());
        parent::install($context);
    }

    public function uninstall(UninstallContext $context): void
    {
        $this->deleteLanguage($context->getContext());
        $this->deleteBaseSnippetSet($context->getContext());
        parent::uninstall($context);
    }

    private function addLanguage(Context $shopwareContext): void
    {
        $localeId = $this->getLocaleId($shopwareContext);
        if (!$this->isNewLanguage($localeId, $shopwareContext)) {
            return;
        }

        $languageRepository = $this->container->get('language.repository');

        $languageRepository->create([[
            'id' => Uuid::randomHex(),
            'name' => self::SHOPUEBER_LANGUAGE_NAME,
            'localeId' => $localeId,
            'translationCodeId' => $localeId,
            'createdAt' => date(Defaults::STORAGE_DATE_FORMAT),
        ]], $shopwareContext);
    }

    private function addBaseSnippetSet(Context $shopwareContext): void
    {
        $snippetSetRepository = $this->container->get('snippet_set.repository');

        $snippetSetRepository->create([[
            'id' => Uuid::randomHex(),
            'name' => 'Shop-Uebersetzungen.de ' . self::SHOPUEBER_LOCALE_CODE,
            'baseFile' => (new SnippetFile())->getName(),
            'iso' => self::SHOPUEBER_LOCALE_CODE,
            'createdAt' => date(Defaults::STORAGE_DATE_FORMAT),
        ]], $shopwareContext);
    }

    private function deleteLanguage(Context $shopwareContext): void
    {
        $languageRepository = $this->container->get('language.repository');

        $criteria = new Criteria();
        $criteria->addFilter(
            new EqualsFilter('name', self::SHOPUEBER_LANGUAGE_NAME)
        );

        $languageIds = $languageRepository->searchIds($criteria, $shopwareContext)->getData();
        if (empty($languageIds)) {
            return;
        }

        $languageIds = array_values($languageIds);
        $languageRepository->delete($languageIds, $shopwareContext);
    }

    private function deleteBaseSnippetSet(Context $shopwareContext): void
    {
        $snippetSetRepository = $this->container->get('snippet_set.repository');

        $criteria = new Criteria();
        $criteria->addFilter(new MultiFilter('AND', [
            new EqualsFilter('name', 'Shop-Uebersetzungen.de ' . self::SHOPUEBER_LOCALE_CODE),
            new EqualsFilter('baseFile', (new SnippetFile())->getName()),
        ]));

        $setIds = $snippetSetRepository->searchIds($criteria, $shopwareContext)->getData();
        if (empty($setIds)) {
            return;
        }

        $setIds = array_values($setIds);

        $snippetSetRepository->delete($setIds, $shopwareContext);
    }

    private function getLocaleId(Context $shopwareContext): string
    {
        $localeRepository = $this->container->get('locale.repository');

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('code', self::SHOPUEBER_LOCALE_CODE));

        $localeResult = $localeRepository->searchIds($criteria, $shopwareContext);

        if ($localeResult->getTotal() === 0) {
            throw new \RuntimeException('Invalid locale. Please make sure you entered an existing locale with the correct format: xx-XX');
        }

        $firstId = $localeResult->firstId();
        if ($firstId === null) {
            throw new \RuntimeException('Invalid locale. Please make sure you entered an existing locale with the correct format: xx-XX');
        }

        return $firstId;
    }

    private function isNewLanguage(string $localeId, Context $shopwareContext): bool
    {
        $languageRepository = $this->container->get('language.repository');

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('localeId', $localeId));

        $languageResult = $languageRepository->searchIds($criteria, $shopwareContext);

        return $languageResult->getTotal() === 0;
    }
}
