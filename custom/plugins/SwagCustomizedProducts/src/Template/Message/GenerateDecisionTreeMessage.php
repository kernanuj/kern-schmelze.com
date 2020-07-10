<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Template\Message;

use Shopware\Core\Framework\Context;

class GenerateDecisionTreeMessage
{
    /**
     * @var string
     */
    private $templateId;

    /**
     * @var string
     */
    private $contextData;

    public function __construct(string $templateId)
    {
        $this->templateId = $templateId;
    }

    public function getContextData(): string
    {
        return $this->contextData;
    }

    public function setContextData(string $contextData): void
    {
        $this->contextData = $contextData;
    }

    public function getTemplateId(): string
    {
        return $this->templateId;
    }

    public function withContext(Context $context): GenerateDecisionTreeMessage
    {
        $this->contextData = \serialize($context);

        return $this;
    }

    public function readContext(): Context
    {
        return \unserialize($this->contextData);
    }
}
