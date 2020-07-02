<?php

declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagSocialShopping\Component\Validation;

use IteratorAggregate;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use SwagSocialShopping\DataAbstractionLayer\Entity\SocialShoppingSalesChannelEntity;
use function count;

class NetworkProductValidator
{
    /**
     * @var IteratorAggregate|NetworkProductValidatorInterface[]
     */
    private $validators;

    /**
     * @var EntityRepositoryInterface
     */
    private $socialShoppingErrorRepository;

    public function __construct( IteratorAggregate $validators, EntityRepositoryInterface $socialShoppingErrorRepository)
    {
        $this->validators = $validators;
        $this->socialShoppingErrorRepository = $socialShoppingErrorRepository;
    }

    public function executeValidators(
        EntityCollection $productCollection,
        SocialShoppingSalesChannelEntity $socialShoppingSalesChannelEntity,
        bool $clearErrors = true
    ): bool {
        $context = Context::createDefaultContext();
        $hasErrors = false;

        if ($clearErrors) {
            $this->clearErrors($socialShoppingSalesChannelEntity->getSalesChannelId(), $context);
        }

        $configuration = $socialShoppingSalesChannelEntity->getConfiguration();
        if ($configuration === null || !isset($configuration['includeVariants'])) {
            $includeVariants = false;
        } else {
            $includeVariants = $configuration['includeVariants'];
        }

        foreach ($this->validators as $validator) {
            if (!$validator->supports($socialShoppingSalesChannelEntity->getNetwork())) {
                continue;
            }

            foreach ($productCollection->getElements() as $productEntity) {
                if ($includeVariants && !$productEntity->getParentId() && $productEntity->getChildCount() > 0) {
                    continue; // Skip main product if variants are included
                }
                if (!$includeVariants && $productEntity->getParentId()) {
                    continue; // Skip variants unless they are included
                }
                $validationResult = $validator->validate($productEntity, $socialShoppingSalesChannelEntity);

                if ($validationResult->hasErrors()) {
                    $this->writeError($validationResult, $productEntity, $socialShoppingSalesChannelEntity, $context);
                    $hasErrors = true;
                }
            }
        }

        return $hasErrors;
    }

    public function clearErrors(string $socialShoppingSalesChannelId, Context $context): void
    {
        $criteria = new Criteria();
        $criteria->addFilter(
            new EqualsFilter('salesChannelId', $socialShoppingSalesChannelId)
        );

        $ids = $this->socialShoppingErrorRepository->searchIds($criteria, $context);

        if ( count($ids->getData()) === 0) {
            return;
        }

        $this->socialShoppingErrorRepository->delete(array_values($ids->getData()), $context);
    }

    private function writeError(NetworkProductValidationResult $result, ProductEntity $productEntity, SocialShoppingSalesChannelEntity $socialShoppingSalesChannelEntity, Context $context): void
    {
        $this->socialShoppingErrorRepository->create(
            [
                [
                    'productId' => $productEntity->getId(),
                    'productVersionId' => $productEntity->getVersionId(),
                    'salesChannelId' => $socialShoppingSalesChannelEntity->getSalesChannelId(),
                    'errors' => $result->getErrors()->jsonSerialize(),
                ],
            ],
            $context
        );
    }
}
