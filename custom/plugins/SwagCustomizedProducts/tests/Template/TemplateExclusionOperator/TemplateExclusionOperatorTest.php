<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Test\Template\TemplateExclusionOperator;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Uuid\Uuid;
use Swag\CustomizedProducts\Test\Helper\ServicesTrait;

class TemplateExclusionOperatorTest extends TestCase
{
    use ServicesTrait;

    /**
     * @var EntityRepositoryInterface
     */
    private $templateExclusionOperatorRepository;

    /**
     * @var EntityRepositoryInterface
     */
    private $templateExclusionOperatorTranslationRepository;

    protected function setUp(): void
    {
        $this->templateExclusionOperatorRepository = $this->getContainer()->get('swag_customized_products_template_exclusion_operator.repository');
        $this->templateExclusionOperatorTranslationRepository = $this->getContainer()->get('swag_customized_products_template_exclusion_operator_translation.repository');
    }

    public function testAssertOperatorCount(): void
    {
        // First we check if we got the expected count of operators
        $context = Context::createDefaultContext();
        $ids = $this->templateExclusionOperatorRepository->searchIds(new Criteria(), $context)->getIds();
        static::assertCount(20, $ids);

        // Second we check that we have exact twice the amount of translations as operators ( Each Operator in English and German )
        $translationIds = $this->templateExclusionOperatorTranslationRepository->searchIds(new Criteria(), $context)->getIds();
        static::assertCount(2 * \count($ids), $translationIds);
    }

    public function testThatOperatorsCanGetAssignedToACondition(): void
    {
        $context = Context::createDefaultContext();
        $optionId = Uuid::randomHex();

        $this->createTemplate(
            Uuid::randomHex(),
            $context,
            [
                'options' => [
                    [
                        'id' => $optionId,
                        'type' => 'checkbox',
                        'displayName' => 'ExampleOption',
                        'position' => 1,
                        'typeProperties' => [],
                    ],
                ],
                'exclusions' => [
                    [
                        'name' => 'firstExclusion',
                        'conditions' => [
                            [
                                'templateOptionId' => $optionId,
                                'templateExclusionOperator' => [
                                    'operator' => 'XXX',
                                    'label' => 'ExampleOperator',
                                    'templateOptionType' => 'checkbox',
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        );
        static::assertTrue(true);
    }

    // ToDo@SEG: Make sure that neither operators nor translations can get altered by the user
}
