<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CmsExtensions\Test\Storefront\Pagelet\Quickview;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Content\Product\Exception\ProductNotFoundException;
use Shopware\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Storefront\Test\Page\StorefrontPageTestBehaviour;
use Swag\CmsExtensions\Storefront\Pagelet\Quickview\QuickviewPageletLoaderInterface;
use Swag\CmsExtensions\Storefront\Pagelet\Quickview\QuickviewVariantPageletLoader;
use Symfony\Component\HttpFoundation\Request;

class QuickviewVariantPageletLoaderTest extends TestCase
{
    use IntegrationTestBehaviour;
    use StorefrontPageTestBehaviour;

    /**
     * @var QuickviewPageletLoaderInterface
     */
    private $quickviewVariantPageletLoader;

    public function setUp(): void
    {
        $this->quickviewVariantPageletLoader = $this->getContainer()->get(QuickviewVariantPageletLoader::class);
    }

    public function testItRequiresAnExistingCombination(): void
    {
        $context = $this->createSalesChannelContext();
        $product = $this->getRandomProduct($context);

        $groupId = Uuid::randomHex();
        $request = new Request([
            'options' => $this->getRandomOptions($groupId),
            'switched' => $groupId,
            'parentId' => $product->getId(),
        ], [], [
            'productId' => $product->getId(),
        ]);

        $this->expectException(ProductNotFoundException::class);
        $this->getPageLoader()->load($request, $context);
    }

    protected function getPageLoader(): QuickviewPageletLoaderInterface
    {
        return $this->quickviewVariantPageletLoader;
    }

    private function getRandomOptions(string $groupId): string
    {
        return json_encode([
            $groupId => Uuid::randomHex(),
            Uuid::randomHex() => Uuid::randomHex(),
            Uuid::randomHex() => Uuid::randomHex(),
        ]) ?: '';
    }
}
