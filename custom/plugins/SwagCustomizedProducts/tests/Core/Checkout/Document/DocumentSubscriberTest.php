<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Test\Core\Checkout\Document;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Checkout\Document\Event\DocumentOrderCriteriaEvent;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use Swag\CustomizedProducts\Core\Checkout\Document\DocumentSubscriber;

class DocumentSubscriberTest extends TestCase
{
    use IntegrationTestBehaviour;

    /**
     * @var DocumentSubscriber
     */
    private $documentSubscriber;

    protected function setUp(): void
    {
        $this->documentSubscriber = $this->getContainer()->get(DocumentSubscriber::class);
    }

    public function testGetSubscribedEvents(): void
    {
        static::assertSame(
            [
                DocumentOrderCriteriaEvent::class => 'addCustomizedProducts',
            ],
            $this->documentSubscriber::getSubscribedEvents()
        );
    }

    public function testAddCustomizedProducts(): void
    {
        $event = new DocumentOrderCriteriaEvent(
            new Criteria(),
            Context::createDefaultContext()
        );
        $this->documentSubscriber->addCustomizedProducts($event);

        $criteria = $event->getCriteria();
        static::assertNotNull($criteria->getAssociations());
    }
}
