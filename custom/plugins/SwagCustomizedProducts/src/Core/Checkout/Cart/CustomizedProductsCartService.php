<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Core\Checkout\Cart;

use Exception;
use HTMLPurifier;
use HTMLPurifier_Config;
use Psr\Log\LoggerInterface;
use Shopware\Core\Checkout\Cart\Exception\InvalidPayloadException;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Swag\CustomizedProducts\Core\Checkout\Cart\Error\SwagCustomizedProductsMaxFileCountExceededError;
use Swag\CustomizedProducts\Core\Checkout\CustomizedProductsCartDataCollector;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\TemplateOptionCollection;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\TemplateOptionEntity;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\Type\FileUpload;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\Type\HtmlEditor;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\Type\ImageUpload;
use function array_filter;
use function count;
use function in_array;
use function is_string;
use function sprintf;

class CustomizedProductsCartService implements CustomizedProductCartServiceInterface
{
    /**
     * @var EntityRepositoryInterface
     */
    private $templateOptionRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(EntityRepositoryInterface $templateOptionRepository, LoggerInterface $logger)
    {
        $this->templateOptionRepository = $templateOptionRepository;
        $this->logger = $logger;
    }

    public function createCustomizedProductsLineItem(
        string $customizedProductsTemplateId,
        string $productId,
        int $productQuantity
    ): LineItem {
        $customizedProductsLineItem = new LineItem(
            Uuid::randomHex(),
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_TEMPLATE_LINE_ITEM_TYPE,
            $customizedProductsTemplateId,
            $productQuantity
        );
        $customizedProductsLineItem->setRemovable(true);

        $productLineItem = new LineItem(
            Uuid::randomHex(),
            LineItem::PRODUCT_LINE_ITEM_TYPE,
            $productId,
            $productQuantity
        );

        $customizedProductsLineItem->addChild($productLineItem);

        return $customizedProductsLineItem;
    }

    public function loadOptionEntities(string $templateId, RequestDataBag $options, Context $context): TemplateOptionCollection
    {
        $optionIds = $options->keys();
        $optionIds = array_filter($optionIds, static function ($id) {
            return Uuid::isValid($id);
        });

        if ( count($optionIds) < 1) {
            return new TemplateOptionCollection();
        }

        $criteria = new Criteria($optionIds);
        $criteria->addFilter(
            new EqualsFilter('templateId', $templateId)
        );

        /** @var TemplateOptionCollection $optionCollection */
        $optionCollection = $this->templateOptionRepository->search($criteria, $context)->getEntities();

        return $optionCollection;
    }

    public function validateOptionValues(RequestDataBag $options, TemplateOptionCollection $optionEntities): RequestDataBag
    {
        foreach ($options as $optionId => $optionData) {
            $entity = $optionEntities->get($optionId);
            if ($entity === null) {
                $options->remove($optionId);
                continue;
            }

            $type = $entity->getType();

            if ($type === HtmlEditor::NAME) {
                $this->validateHtml($optionData);
            }
        }

        return $options;
    }

    public function addOptions(
        LineItem $customizedProductsLineItem,
        RequestDataBag $options,
        int $productQuantity,
        TemplateOptionCollection $optionEntities
    ): void {
        foreach ($options as $optionId => $option) {
            $value = $option->get('value');
            $values = $option->get('values');

            $optionLineItem = new LineItem(
                Uuid::randomHex(),
                CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_OPTION_LINE_ITEM_TYPE,
                (string) $optionId,
                $productQuantity
            );

            $entity = $optionEntities->get($optionId);
            if ($entity === null) {
                throw new Exception('Option entity not found');
            }

            if ( in_array($entity->getType(), [ImageUpload::NAME, FileUpload::NAME], true)) {
                $this->enrichOptionLineItemWithMedia($entity, $option, $optionLineItem);

                $customizedProductsLineItem->addChild($optionLineItem);
                continue;
            }

            if (empty($value) && empty($values)) {
                continue;
            }

            // Checkboxes data structure
            if (!empty($values)) {
                $this->addOptionValues($optionLineItem, $values);
            }

            // Radiobuttons, Dropdown data structure
            if ($value !== null && is_string($value) && Uuid::isValid($value)) {
                $this->addOptionValue($optionLineItem, $value);
            }

            try {
                $optionLineItem->setPayloadValue('value', $value);
            } catch (InvalidPayloadException $e) {
                $this->logger->warning(
                    sprintf(
                        'The provided value for option "%s" is invalid. Exception message: %s',
                        $optionId,
                        $e->getMessage()
                    )
                );
                continue;
            }

            $customizedProductsLineItem->addChild($optionLineItem);
        }
    }

    private function validateHtml(RequestDataBag $optionData): void
    {
        $value = $optionData->get('value');

        $config = HTMLPurifier_Config::createDefault();
        $config->set('HTML.AllowedElements', HtmlEditor::ALLOWED_ELEMENTS);
        $config->set('HTML.AllowedAttributes', HtmlEditor::ALLOWED_ATTRIBUTES);

        $purifier = new HTMLPurifier($config);
        $value = $purifier->purify($value);

        $optionData->set('value', $value);
    }

    private function addOptionValues(LineItem $optionLineItem, RequestDataBag $values): void
    {
        /** @var RequestDataBag $optionValue */
        foreach ($values as $optionValueId => $optionValue) {
            $value = $optionValue->get('value');
            if (empty($value) || empty($optionValueId)) {
                continue;
            }

            $optionLineItem->addChild(new LineItem(
                Uuid::randomHex(),
                CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_OPTION_VALUE_LINE_ITEM_TYPE,
                (string) $optionValueId,
                $optionLineItem->getQuantity()
            ));
        }
    }

    private function addOptionValue(LineItem $optionLineItem, string $value): void
    {
        $optionLineItem->addChild(new LineItem(
            Uuid::randomHex(),
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_OPTION_VALUE_LINE_ITEM_TYPE,
            $value,
            $optionLineItem->getQuantity()
        ));
    }

    /**
     * @throws SwagCustomizedProductsMaxFileCountExceededError
     */
    private function enrichOptionLineItemWithMedia(
        TemplateOptionEntity $optionEntity,
        RequestDataBag $option,
        LineItem $optionLineItem
    ): void {
        /** @var RequestDataBag $mediaArray */
        $mediaArray = $option->get('media');
        $typeProperties = $optionEntity->getTypeProperties();

        if (isset($typeProperties['maxCount'])
            && $typeProperties['maxCount'] > 0
            && count($mediaArray->all()) > $typeProperties['maxCount']
        ) {
            throw new SwagCustomizedProductsMaxFileCountExceededError();
        }

        $mediaToPush = [];
        /** @var RequestDataBag $media */
        foreach ($mediaArray as $media) {
            $mediaToPush[] = [
                'mediaId' => $media->get('id'),
                'filename' => $media->get('filename'),
            ];
        }

        $optionLineItem->setPayloadValue('media', $mediaToPush);
    }
}
