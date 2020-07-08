<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Test\Template\Api;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\Test\TestCaseBase\AdminApiTestBehaviour;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\PlatformRequest;
use Swag\CustomizedProducts\Template\Aggregate\TemplateExclusionOperator\TemplateExclusionOperatorDefinition;
use Swag\CustomizedProducts\Template\TemplateDefinition;
use Swag\CustomizedProducts\Test\Helper\ServicesTrait;
use Symfony\Component\HttpFoundation\Response;
use function sprintf;

class TemplateControllerTest extends TestCase
{
    use AdminApiTestBehaviour;
    use ServicesTrait;

    /**
     * @var EntityRepositoryInterface
     */
    private $templateRepository;

    /**
     * @var EntityRepositoryInterface
     */
    private $operatorRepository;

    protected function setUp(): void
    {
        $container = $this->getContainer();
        $this->templateRepository = $container->get(TemplateDefinition::ENTITY_NAME . '.repository');
        $this->operatorRepository = $container->get(TemplateExclusionOperatorDefinition::ENTITY_NAME . '.repository');
    }

    public function testAddTreeGenerationMessageToQueue(): void
    {
        $templateId = Uuid::randomHex();
        $firstOptionId = Uuid::randomHex();
        $secondOptionId = Uuid::randomHex();
        $this->createTemplate(
            $templateId,
            $this->templateRepository,
            Context::createDefaultContext(),
            [
                'options' => [
                    [
                        'id' => $firstOptionId,
                        'type' => 'checkbox',
                        'typeProperties' => [],
                        'displayName' => 'Checkbox1',
                        'position' => 1,
                    ],
                    [
                        'id' => $secondOptionId,
                        'type' => 'checkbox',
                        'typeProperties' => [],
                        'displayName' => 'Checkbox2',
                        'position' => 2,
                    ],
                ],
                'exclusions' => [
                    [
                        'name' => 'firstEverTemplateExclusion',
                        'conditions' => [
                            [
                                'templateOptionId' => $firstOptionId,
                                'templateExclusionOperatorId' => $this->getOperatorIdForType($this->operatorRepository),
                            ],
                            [
                                'templateOptionId' => $secondOptionId,
                                'templateExclusionOperatorId' => $this->getOperatorIdForType($this->operatorRepository),
                            ],
                        ],
                    ],
                ],
            ]
        );

        $this->getBrowser()->request(
            'POST',
            sprintf('/api/v' . PlatformRequest::API_VERSION . '/_action/swag-customized-products-template/%s/tree', $templateId)
        );

        static::assertSame(
            Response::HTTP_NO_CONTENT,
            $this->getBrowser()->getResponse()->getStatusCode(),
            (string) $this->getBrowser()->getResponse()->getContent()
        );
    }
}
