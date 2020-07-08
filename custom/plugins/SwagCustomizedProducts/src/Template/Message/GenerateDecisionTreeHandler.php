<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Template\Message;

use Shopware\Core\Framework\MessageQueue\Handler\AbstractMessageHandler;
use Swag\CustomizedProducts\Template\TemplateDecisionTreeGeneratorInterface;

class GenerateDecisionTreeHandler extends AbstractMessageHandler
{
    /**
     * @var TemplateDecisionTreeGeneratorInterface
     */
    private $treeGenerator;

    public function __construct(TemplateDecisionTreeGeneratorInterface $treeGenerator)
    {
        $this->treeGenerator = $treeGenerator;
    }

    /**
     * @param GenerateDecisionTreeMessage|mixed $message
     */
    public function handle($message): void
    {
        if (!($message instanceof GenerateDecisionTreeMessage)) {
            return;
        }

        $this->treeGenerator->generate($message->getTemplateId(), $message->readContext());
    }

    public static function getHandledMessages(): iterable
    {
        return [
            GenerateDecisionTreeMessage::class,
        ];
    }
}
