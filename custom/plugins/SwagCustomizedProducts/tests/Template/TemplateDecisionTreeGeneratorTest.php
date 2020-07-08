<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Test\Template;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Uuid\Uuid;
use Swag\CustomizedProducts\Template\Aggregate\TemplateExclusionOperator\TemplateExclusionOperatorDefinition;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\Type\Checkbox;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\Type\Select;
use Swag\CustomizedProducts\Template\TemplateDecisionTreeGenerator;
use Swag\CustomizedProducts\Template\TemplateDecisionTreeGeneratorInterface;
use Swag\CustomizedProducts\Template\TemplateDefinition;
use Swag\CustomizedProducts\Template\TemplateEntity;
use Swag\CustomizedProducts\Test\Helper\ServicesTrait;
use function array_keys;
use function in_array;
use function random_int;

class TemplateDecisionTreeGeneratorTest extends TestCase
{
    use ServicesTrait;

    /**
     * @var TemplateDecisionTreeGeneratorInterface
     */
    private $generator;

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
        $this->generator = $container->get(TemplateDecisionTreeGenerator::class);
        $this->templateRepository = $container->get(TemplateDefinition::ENTITY_NAME . '.repository');
        $this->operatorRepository = $container->get(TemplateExclusionOperatorDefinition::ENTITY_NAME . '.repository');
    }

    public function testGenerate(): void
    {
        $templateId = Uuid::randomHex();
        $firstCheckboxOptionId = Uuid::randomHex();
        $secondCheckboxOptionId = Uuid::randomHex();
        $selectOptionId = Uuid::randomHex();
        $checkboxOperator = $this->getOperatorIdForType($this->operatorRepository);
        $selectOperator = $this->getOperatorIdForType($this->operatorRepository, Select::NAME);
        $context = Context::createDefaultContext();
        $this->createTemplate(
            $templateId,
            $this->templateRepository,
            $context,
            [
                'options' => [
                    [
                        'id' => $firstCheckboxOptionId,
                        'type' => Checkbox::NAME,
                        'typeProperties' => [],
                        'displayName' => 'Checkbox1',
                        'position' => 1,
                    ],
                    [
                        'id' => $secondCheckboxOptionId,
                        'type' => Checkbox::NAME,
                        'typeProperties' => [],
                        'displayName' => 'Checkbox1',
                        'position' => 1,
                    ],
                    [
                        'id' => $selectOptionId,
                        'type' => Select::NAME,
                        'typeProperties' => [],
                        'displayName' => 'Select',
                        'position' => 2,
                        'values' => [
                            [
                                'displayName' => 'Shopware blue',
                                'position' => 1,
                            ],
                            [
                                'displayName' => 'Shopware violette',
                                'position' => 2,
                            ],
                        ],
                    ],
                ],
                'exclusions' => [
                    [
                        'name' => 'firstEverTemplateExclusion',
                        'conditions' => [
                            [
                                'templateOptionId' => $firstCheckboxOptionId,
                                'templateExclusionOperatorId' => $checkboxOperator,
                            ],
                            [
                                'templateOptionId' => $secondCheckboxOptionId,
                                'templateExclusionOperatorId' => $checkboxOperator,
                            ],
                            [
                                'templateOptionId' => $selectOptionId,
                                'templateExclusionOperatorId' => $selectOperator,
                            ],
                        ],
                    ],
                ],
            ]
        );

        // Generate tree and assert correct structure
        $tree = $this->generator->generate($templateId, $context);
        $this->assertTree($tree, $firstCheckboxOptionId, $secondCheckboxOptionId, $selectOptionId);

        // Assert tree actually gets saved
        /** @var TemplateEntity|null $template */
        $template = $this->templateRepository->search(new Criteria([$templateId]), $context)->first();
        static::assertNotNull($template);
        $decisionTree = $template->getDecisionTree();
        static::assertNotNull($decisionTree);
        static::assertNotEmpty($decisionTree);
    }

    /**
     * Warning! Produces approximately 104MB of tree data
     */
    public function testGenerateBenchmark(): void
    {
        static::markTestSkipped('Test skipped on ci worker because of memory load.');
        $templateId = Uuid::randomHex();
        $context = Context::createDefaultContext();
        $this->createTemplate(
            $templateId,
            $this->templateRepository,
            $context,
            $this->getBenchmarkData()
        );

        $tree = $this->generator->generate($templateId, $context);
        static::assertCount(100, $tree);
    }

    /**
     * Returns 100 options and 100 exclusions with 10 conditions each
     */
    private function getBenchmarkData(): array
    {
        $data = [
            'options' => [],
            'exclusions' => [],
        ];

        for ($i = 0; $i < 100; ++$i) {
            $data['options'][] = [
                'id' => Uuid::randomHex(),
                'type' => 'checkbox',
                'typeProperties' => [],
                'displayName' => 'Checkbox' . $i,
                'position' => $i,
            ];
        }

        for ($i = 0; $i < 100; ++$i) {
            $conditions = [];
            for ($j = 0; $j < 10; ++$j) {
                $conditions[] = [
                    'templateOptionId' => $data['options'][random_int(0, 99)]['id'],
                    'templateExclusionOperatorId' => $this->getOperatorIdForType($this->operatorRepository),
                ];
            }

            $data['exclusions'][] = [
                'name' => 'firstEverTemplateExclusion',
                'conditions' => $conditions,
            ];
        }

        return $data;
    }

    private function assertTree(array $tree, string $firstOptionId, string $secondOptionId, string $thirdOptionId): void
    {
        static::assertCount(1, array_keys($tree));
        foreach ($tree as $leaf) {
            $optionIds = [$firstOptionId, $secondOptionId, $thirdOptionId];

            static::assertIsArray($leaf);
            static::assertCount(3, $leaf);
            foreach ($leaf as $condition) {
                static::assertIsArray($condition);
                static::assertCount(3, $condition);
                static::assertArrayHasKey('id', $condition);
                $id = $condition['id'];
                static::assertContains($id, $optionIds);
                static::assertArrayHasKey('type', $condition);
                if ( in_array($id, [$firstOptionId, $secondOptionId], true)) {
                    static::assertEquals(Checkbox::NAME, $condition['type']);
                } else {
                    static::assertEquals(Select::NAME, $condition['type']);
                }
                static::assertArrayHasKey('operator', $condition);
                $operator = $condition['operator'];
                static::assertIsArray($operator);
                static::assertArrayHasKey('type', $operator);
                $type = $operator['type'];
                static::assertContains($type, ['X', '!X']);
            }
        }
    }
}
