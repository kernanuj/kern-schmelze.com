<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Test\Util\Lifecycle;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\ResultStatement;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Content\Media\Aggregate\MediaFolder\MediaFolderCollection;
use Shopware\Core\Content\Media\Aggregate\MediaFolder\MediaFolderEntity;
use Shopware\Core\Content\Media\MediaCollection;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Framework\Uuid\Uuid;
use Swag\CustomizedProducts\Util\Lifecycle\Uninstaller;

class UninstallerTest extends TestCase
{
    public function testUninstall(): void
    {
        $connection = $this->getConnectionMock();
        $resultStatement = $this->getResultStatementMock();
        $mediaFolderRepositoryMock = $this->getEntityRepositoryMock();
        $entitySearchResult = $this->getEntitySearchResultMock();

        $connection->expects(static::atLeast(8))
            ->method('executeUpdate')
            ->willReturn(1);

        $resultStatement->method('fetchColumn')
            ->willReturn('exampleColumm');

        $connection->method('executeQuery')
            ->willReturn($resultStatement);

        $repositoryMock = $this->getEntityRepositoryMock();

        $mediaFolderEntity = new MediaFolderEntity();
        $mediaFolderEntity->setMedia(new MediaCollection());
        $mediaFolderEntity->setId(Uuid::randomHex());
        $mediaFolderCollection = new MediaFolderCollection([$mediaFolderEntity]);

        $entitySearchResult->method('getEntities')
            ->willReturn($mediaFolderCollection);

        $mediaFolderRepositoryMock->method('search')
            ->willReturn($entitySearchResult);

        $uninstaller = new Uninstaller(
            $mediaFolderRepositoryMock,
            $repositoryMock,
            $repositoryMock,
            $repositoryMock,
            $connection
        );

        $uninstaller->uninstall($this->getContextMock());
    }

    /**
     * @return Context|MockObject
     */
    private function getContextMock(): Context
    {
        return $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return Connection|MockObject
     */
    private function getConnectionMock(): Connection
    {
        return $this->getMockBuilder(Connection::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['executeUpdate', 'executeQuery'])
            ->getMock();
    }

    /**
     * @return EntityRepositoryInterface|MockObject
     */
    private function getEntityRepositoryMock(): EntityRepositoryInterface
    {
        return $this->getMockBuilder(EntityRepositoryInterface::class)->getMock();
    }

    /**
     * @return ResultStatement|MockObject
     */
    private function getResultStatementMock(): ResultStatement
    {
        return $this->getMockBuilder(ResultStatement::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return EntitySearchResult|MockObject
     */
    private function getEntitySearchResultMock(): EntitySearchResult
    {
        return $this->getMockBuilder(EntitySearchResult::class)
            ->disableOriginalConstructor()
            ->getMock();
    }
}
