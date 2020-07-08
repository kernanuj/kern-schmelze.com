<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Test\Template\Message;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Uuid\Uuid;
use Swag\CustomizedProducts\Template\Message\GenerateDecisionTreeHandler;
use Swag\CustomizedProducts\Template\Message\GenerateDecisionTreeMessage;
use Swag\CustomizedProducts\Template\TemplateDecisionTreeGenerator;
use Swag\CustomizedProducts\Template\TemplateDecisionTreeGeneratorInterface;
use Symfony\Bridge\PsrHttpMessage\Tests\Fixtures\Message;

class GenerateDecisionTreeHandlerTest extends TestCase
{
    public function testHandlerDoesNotCallGenerateOnWrongMessage(): void
    {
        /** @var MockObject|TemplateDecisionTreeGenerator $generator */
        $generator = $this->createMock(TemplateDecisionTreeGeneratorInterface::class);
        $handler = new GenerateDecisionTreeHandler($generator);

        /** @var GenerateDecisionTreeMessage $msg */
        $msg = new Message();

        $generator->expects(static::never())->method('generate')->withAnyParameters();
        $handler->handle($msg);
    }

    public function testHandlerCallsGenerate(): void
    {
        /** @var MockObject|TemplateDecisionTreeGenerator $generator */
        $generator = $this->createMock(TemplateDecisionTreeGeneratorInterface::class);
        $handler = new GenerateDecisionTreeHandler($generator);
        $templateId = Uuid::randomHex();
        $context = Context::createDefaultContext();
        $msg = new GenerateDecisionTreeMessage($templateId);

        $generator->expects(static::once())->method('generate')->with($templateId, $context);
        $handler->handle($msg->withContext($context));
    }
}
