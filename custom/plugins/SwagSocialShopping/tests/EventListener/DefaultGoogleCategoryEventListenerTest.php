<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\SocialShopping\Test\EventListener;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Write\Command\DeleteCommand;
use Shopware\Core\Framework\DataAbstractionLayer\Write\Command\InsertCommand;
use Shopware\Core\Framework\DataAbstractionLayer\Write\Command\WriteCommand;
use Shopware\Core\Framework\DataAbstractionLayer\Write\EntityExistence;
use Shopware\Core\Framework\DataAbstractionLayer\Write\Validation\PreWriteValidationEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Write\WriteContext;
use Shopware\Core\Framework\Test\TestCaseBase\KernelTestBehaviour;
use Shopware\Core\Framework\Uuid\Uuid;
use SwagSocialShopping\Component\Network\Facebook;
use SwagSocialShopping\DataAbstractionLayer\Entity\SocialShoppingSalesChannelDefinition;
use SwagSocialShopping\EventListener\DefaultGoogleCategoryEventListener;

class DefaultGoogleCategoryEventListenerTest extends TestCase
{
    use KernelTestBehaviour;

    public function testPreValidateWithoutCommands(): void
    {
        $commands = [];
        $preWriteValidationEvent = $this->createPreWriteValidationEvent($commands);
        $this->createListener()->preValidate($preWriteValidationEvent);

        static::assertCount(0, $preWriteValidationEvent->getExceptions()->getExceptions());
    }

    public function testPreValidateWithDeleteCommand(): void
    {
        $definition = new SocialShoppingSalesChannelDefinition();
        $entityExistence = $this->createEntityExistence($definition);
        $commands = [new DeleteCommand($definition, ['id' => 'test-id'], $entityExistence)];
        $preWriteValidationEvent = $this->createPreWriteValidationEvent($commands);

        $this->createListener()->preValidate($preWriteValidationEvent);

        static::assertCount(0, $preWriteValidationEvent->getExceptions()->getExceptions());
    }

    public function testPreValidateWithInsertCommandWrongDefinition(): void
    {
        $payload = [];
        $primaryKey = ['id' => 'test-id'];
        $commands = $this->createCommandsArray($payload, $primaryKey, true);

        $preWriteValidationEvent = $this->createPreWriteValidationEvent($commands);
        $this->createListener()->preValidate($preWriteValidationEvent);

        static::assertCount(0, $preWriteValidationEvent->getExceptions()->getExceptions());
    }

    public function testPreValidateWithoutConfigurationPayload(): void
    {
        $payload = [];
        $primaryKey = ['id' => 'test-id'];
        $commands = $this->createCommandsArray($payload, $primaryKey);

        $preWriteValidationEvent = $this->createPreWriteValidationEvent($commands);
        $this->createListener()->preValidate($preWriteValidationEvent);

        static::assertCount(0, $preWriteValidationEvent->getExceptions()->getExceptions());
    }

    public function testPreValidateInvalidSalesChannelId(): void
    {
        $payload = ['configuration' => []];
        $primaryKey = ['id' => Uuid::randomBytes()];
        $commands = $this->createCommandsArray($payload, $primaryKey);

        $preWriteValidationEvent = $this->createPreWriteValidationEvent($commands);

        $this->expectException( RuntimeException::class);
        $this->expectExceptionMessage('Network not specified');
        $this->createListener()->preValidate($preWriteValidationEvent);
    }

    public function testPreValidateWithInvalidNetworkInPayload(): void
    {
        $payload = ['configuration' => json_encode([]), 'network' => 'FooNetwork'];
        $primaryKey = ['id' => Uuid::randomBytes()];
        $commands = $this->createCommandsArray($payload, $primaryKey);

        $preWriteValidationEvent = $this->createPreWriteValidationEvent($commands);
        $this->createListener()->preValidate($preWriteValidationEvent);

        static::assertCount(0, $preWriteValidationEvent->getExceptions()->getExceptions());
    }

    public function testPreValidateWithNetworkInPayloadWithInvalidConfiguration(): void
    {
        $payload = ['configuration' => json_encode([]), 'network' => Facebook::class];
        $primaryKey = ['id' => Uuid::randomBytes()];
        $commands = $this->createCommandsArray($payload, $primaryKey);

        $preWriteValidationEvent = $this->createPreWriteValidationEvent($commands);
        $this->createListener()->preValidate($preWriteValidationEvent);

        $exceptions = $preWriteValidationEvent->getExceptions();
        static::assertCount(1, $exceptions->getExceptions());
        static::assertSame(
            "There are 1 error(s) while writing data.\n\n1. [] The network needs a default google product category",
            $exceptions->getMessage()
        );
    }

    public function testPreValidateWithNetworkInPayloadWithValidConfiguration(): void
    {
        $payload = [
            'configuration' => json_encode(['defaultGoogleProductCategory' => 123]),
            'network' => Facebook::class,
        ];
        $primaryKey = ['id' => Uuid::randomBytes()];
        $commands = $this->createCommandsArray($payload, $primaryKey);

        $preWriteValidationEvent = $this->createPreWriteValidationEvent($commands);
        $this->createListener()->preValidate($preWriteValidationEvent);

        static::assertCount(0, $preWriteValidationEvent->getExceptions()->getExceptions());
    }

    private function createCommandsArray(
        array $payload,
        array $primaryKey,
        bool $useProductDefinition = false
    ): array {
        $definition = new SocialShoppingSalesChannelDefinition();
        if ($useProductDefinition) {
            $definition = new ProductDefinition();
        }
        $entityExistence = $this->createEntityExistence($definition);

        return [new InsertCommand($definition, $payload, $primaryKey, $entityExistence, '')];
    }

    private function createEntityExistence(EntityDefinition $definition): EntityExistence
    {
        return new EntityExistence($definition->getEntityName(), ['id' => 'test-id'], true, false, false, []);
    }

    /**
     * @param WriteCommand[] $commands
     */
    private function createPreWriteValidationEvent(array $commands): PreWriteValidationEvent
    {
        $writeContext = $this->createWriteContext();

        return new PreWriteValidationEvent($writeContext, $commands);
    }

    private function createWriteContext(): WriteContext
    {
        $context = Context::createDefaultContext();

        return WriteContext::createFromContext($context);
    }

    private function createListener(): DefaultGoogleCategoryEventListener
    {
        /** @var EntityRepositoryInterface $socialShoppingSalesChannelRepo */
        $socialShoppingSalesChannelRepo = $this->getContainer()->get('swag_social_shopping_sales_channel.repository');

        return new DefaultGoogleCategoryEventListener($socialShoppingSalesChannelRepo);
    }
}
