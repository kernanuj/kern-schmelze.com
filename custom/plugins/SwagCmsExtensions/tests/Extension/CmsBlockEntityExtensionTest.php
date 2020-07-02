<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CmsExtensions\Test\Extension;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Content\Cms\Aggregate\CmsBlock\CmsBlockDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Swag\CmsExtensions\Extension\CmsBlockEntityExtension;
use Swag\CmsExtensions\Quickview\QuickviewDefinition;

class CmsBlockEntityExtensionTest extends TestCase
{
    public function testExtendFieldsAddsOneToOneAssociationField(): void
    {
        $collection = $this->getMockBuilder(FieldCollection::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['add'])
            ->getMock();

        $collection
            ->expects(static::once())
            ->method('add')
            ->with(
                new OneToOneAssociationField(
                    CmsBlockEntityExtension::QUICKVIEW_ASSOCIATION_PROPERTY_NAME,
                    'id',
                    QuickviewDefinition::CMS_BLOCK_FOREIGN_KEY_STORAGE_NAME,
                    QuickviewDefinition::class,
                    false
                )
            );

        (new CmsBlockEntityExtension())->extendFields($collection);
    }

    public function testGetDefinitionClassReturnsCmsBlockDefinitionClass(): void
    {
        static::assertSame(
            CmsBlockDefinition::class,
            (new CmsBlockEntityExtension())->getDefinitionClass()
        );
    }
}
