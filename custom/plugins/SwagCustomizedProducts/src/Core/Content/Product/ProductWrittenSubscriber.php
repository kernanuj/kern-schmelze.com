<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Core\Content\Product;

use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Content\Product\ProductEvents;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Swag\CustomizedProducts\Migration\Migration1565933910TemplateProduct;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use function array_key_exists;
use function is_array;

class ProductWrittenSubscriber implements EventSubscriberInterface
{
    public const SWAG_CUSTOMIZED_PRODUCTS_TEMPLATE_INHERITED_CUSTOM_FIELD = 'swagCustomizedProductsTemplateInherited';

    /**
     * @var EntityRepositoryInterface
     */
    private $productRepository;

    public function __construct(EntityRepositoryInterface $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ProductEvents::PRODUCT_WRITTEN_EVENT => [
                ['onProductVariantWrittenInitializeCustomFields'],
                ['onTemplateAssignmentInheritVariants'],
            ],
        ];
    }

    public function onProductVariantWrittenInitializeCustomFields(EntityWrittenEvent $event): void
    {
        if ($event->getEntityName() !== ProductDefinition::ENTITY_NAME) {
            return;
        }

        $updateData = [];
        $fetchedParentIds = [];
        foreach ($event->getPayloads() as $payload) {
            if (! array_key_exists('id', $payload)) {
                continue;
            }

            if (! array_key_exists('parentId', $payload)) {
                continue;
            }

            if ( array_key_exists('swagCustomizedProductsTemplateId', $payload)) {
                continue;
            }

            if (!$this->parentHasTemplateAssociated($payload['parentId'], $fetchedParentIds, $event->getContext())) {
                continue;
            }

            $updateData[] = [
                'id' => $payload['id'],
                'customFields' => [
                    self::SWAG_CUSTOMIZED_PRODUCTS_TEMPLATE_INHERITED_CUSTOM_FIELD => true,
                ],
            ];
        }

        if ($updateData === []) {
            return;
        }

        $this->productRepository->update($updateData, $event->getContext());
    }

    public function onTemplateAssignmentInheritVariants(EntityWrittenEvent $event): void
    {
        if ($event->getEntityName() !== ProductDefinition::ENTITY_NAME) {
            return;
        }

        foreach ($event->getPayloads() as $payload) {
            if (! array_key_exists('id', $payload)) {
                continue;
            }

            if ( array_key_exists('parentId', $payload)) {
                continue;
            }

            if (! array_key_exists('swagCustomizedProductsTemplateId', $payload)) {
                continue;
            }

            $this->inheritExistingVariants($payload['id'], $event->getContext());
        }
    }

    private function parentHasTemplateAssociated(string $parentId, array $fetchedParentIds, Context $context): bool
    {
        if ( array_key_exists($parentId, $fetchedParentIds)) {
            return $fetchedParentIds[$parentId];
        }

        $fetchedParentIds[$parentId] = false;
        $criteria = new Criteria([$parentId]);
        $criteria->setLimit(1);
        $criteria->addAssociation(Migration1565933910TemplateProduct::PRODUCT_TEMPLATE_INHERITANCE_COLUMN);

        $product = $this->productRepository->search($criteria, $context)->first();
        if (!($product instanceof ProductEntity)) {
            return false;
        }

        if (!$product->hasExtension(Migration1565933910TemplateProduct::PRODUCT_TEMPLATE_INHERITANCE_COLUMN)) {
            return false;
        }

        $fetchedParentIds[$parentId] = true;

        return true;
    }

    private function inheritExistingVariants(string $id, Context $context): void
    {
        $criteria = new Criteria();
        $criteria->addFilter(
            new EqualsFilter('parentId', $id)
        );
        $variants = $this->productRepository->search($criteria, $context);
        $updateData = [];

        /** @var ProductEntity $variant */
        foreach ($variants as $variant) {
            $customFields = $variant->getCustomFields();
            if ( is_array($customFields)
                 && array_key_exists(self::SWAG_CUSTOMIZED_PRODUCTS_TEMPLATE_INHERITED_CUSTOM_FIELD, $customFields)
            ) {
                continue;
            }

            $updateData[] = [
                'id' => $variant->getId(),
                'customFields' => [
                    self::SWAG_CUSTOMIZED_PRODUCTS_TEMPLATE_INHERITED_CUSTOM_FIELD => true,
                ],
            ];
        }

        if ($updateData === []) {
            return;
        }

        $this->productRepository->update($updateData, $context);
    }
}
