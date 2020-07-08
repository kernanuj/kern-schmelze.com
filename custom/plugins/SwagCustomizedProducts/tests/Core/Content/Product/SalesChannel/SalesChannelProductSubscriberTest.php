<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Test\Core\Content\Product\SalesChannel;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Content\Product\Events\ProductListingCriteriaEvent;
use Shopware\Core\Content\Product\SalesChannel\SalesChannelProductDefinition;
use Shopware\Core\Content\Product\SalesChannel\SalesChannelProductEntity;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SalesChannel\Context\SalesChannelContextFactory;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Page\Product\ProductLoader;
use Shopware\Storefront\Page\Product\ProductLoaderCriteriaEvent;
use Swag\CustomizedProducts\Core\Content\Product\SalesChannel\SalesChannelProductSubscriber;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\TemplateOptionCollection;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\TemplateOptionEntity;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOptionValue\TemplateOptionValueEntity;
use Swag\CustomizedProducts\Template\TemplateEntity;
use Symfony\Component\HttpFoundation\Request;
use function array_values;

class SalesChannelProductSubscriberTest extends TestCase
{
    use IntegrationTestBehaviour;

    /**
     * @var SalesChannelProductDefinition
     */
    private $salesChannelProductDefinition;

    /**
     * @var SalesChannelContext
     */
    private $salesChannelContext;

    /**
     * @var EntityRepository
     */
    private $productRepo;

    /**
     * @var ProductLoader
     */
    private $productLoader;

    /**
     * @var SalesChannelContextFactory
     */
    private $salesChannelContextFactory;

    /**
     * @var SalesChannelProductSubscriber
     */
    private $subscriber;

    protected function setUp(): void
    {
        $container = $this->getContainer();
        $this->salesChannelContextFactory = $container->get(SalesChannelContextFactory::class);
        $this->salesChannelContext = $this->salesChannelContextFactory->create(
            Uuid::randomHex(),
            Defaults::SALES_CHANNEL
        );
        $this->salesChannelProductDefinition = $container->get(SalesChannelProductDefinition::class);
        $this->productRepo = $container->get('product.repository');
        $this->subscriber = $container->get(SalesChannelProductSubscriber::class);
    }

    public function testAddCustomizedProductsDetailAssociations(): void
    {
        $criteria = new Criteria();
        $event = new ProductLoaderCriteriaEvent(
            $criteria,
            $this->salesChannelContext
        );
        $this->subscriber->addCustomizedProductsDetailAssociations($event);

        static::assertTrue($criteria->hasAssociation('swagCustomizedProductsTemplate'));
        $customizedTemplateCriteria = $criteria->getAssociation('swagCustomizedProductsTemplate');
        static::assertTrue($customizedTemplateCriteria->hasAssociation('options'));

        $customizedTemplateCriteria = $customizedTemplateCriteria->getAssociation('options');
        static::assertTrue($customizedTemplateCriteria->hasAssociation('values'));

        $sorting = $customizedTemplateCriteria->getSorting();
        static::assertCount(1, $sorting);
        static::assertSame('position', $sorting[0]->getField());

        $customizedTemplateCriteria = $customizedTemplateCriteria->getAssociation('values');
        $sorting = $customizedTemplateCriteria->getSorting();
        static::assertCount(1, $sorting);
        static::assertSame('position', $sorting[0]->getField());
    }

    public function testAddCustomizedProductsListingAssociations(): void
    {
        $criteria = new Criteria();
        $event = new ProductListingCriteriaEvent(
            new Request(),
            $criteria,
            $this->salesChannelContext
        );
        $this->subscriber->addCustomizedProductsListingAssociation($event);

        static::assertTrue($criteria->hasAssociation('swagCustomizedProductsTemplate'));
        $customizedTemplateCriteria = $criteria->getAssociation('swagCustomizedProductsTemplate');
        static::assertEmpty($customizedTemplateCriteria->getAssociations());
    }

    public function testIfSortingOfValuesAreCorrect(): void
    {
        $productUuid = Uuid::randomHex();

        $product = require __DIR__ . '/../../../../fixtures/custom_product_data.php';

        $this->productRepo->create([$product], $this->salesChannelContext->getContext());

        $this->salesChannelContext = $this->salesChannelContextFactory->create(
            Uuid::randomHex(),
            Defaults::SALES_CHANNEL
        );
        $this->productLoader = $this->getContainer()->get(ProductLoader::class);

        /** @var SalesChannelProductEntity $loadedProduct */
        $loadedProduct = $this->productLoader->load(
            $productUuid,
            $this->salesChannelContext
        );

        static::assertTrue($loadedProduct->hasExtension('swagCustomizedProductsTemplate'));
        /** @var TemplateEntity $template */
        $template = $loadedProduct->getExtension('swagCustomizedProductsTemplate');
        /** @var TemplateOptionCollection $optionCollection */
        $optionCollection = $template->getOptions();
        /** @var TemplateOptionEntity[] $options */
        $options = array_values($optionCollection->getElements());

        /** @var TemplateOptionValueEntity[] $values */
        $values = array_values($options[0]->get('values')->getElements());

        static::assertSame(1, $values[0]->getPosition());
        static::assertSame(3, $values[1]->getPosition());
        static::assertSame(4, $values[2]->getPosition());
    }
}
