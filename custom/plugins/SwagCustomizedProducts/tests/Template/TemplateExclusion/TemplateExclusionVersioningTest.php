<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Test\Template\TemplateExclusion;

use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Uuid\Uuid;
use Swag\CustomizedProducts\Template\Aggregate\TemplateExclusion\TemplateExclusionDefinition;
use Swag\CustomizedProducts\Template\Aggregate\TemplateExclusion\TemplateExclusionEntity;
use Swag\CustomizedProducts\Template\Aggregate\TemplateExclusionOperator\TemplateExclusionOperatorDefinition;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\Type\Checkbox;
use Swag\CustomizedProducts\Template\TemplateDefinition;
use Swag\CustomizedProducts\Test\Helper\ServicesTrait;

class TemplateExclusionVersioningTest extends TestCase
{
    use ServicesTrait;

    /**
     * @var EntityRepositoryInterface
     */
    private $templateRepository;

    /**
     * @var EntityRepositoryInterface
     */
    private $operatorRepository;

    /**
     * @var EntityRepositoryInterface
     */
    private $exclusionRepository;

    protected function setUp(): void
    {
        $container = $this->getContainer();

        $this->templateRepository = $container->get(TemplateDefinition::ENTITY_NAME . '.repository');
        $this->operatorRepository = $container->get(TemplateExclusionOperatorDefinition::ENTITY_NAME . '.repository');
        $this->exclusionRepository = $container->get(TemplateExclusionDefinition::ENTITY_NAME . '.repository');
    }

    public function testThatTemplateExclusionsVersionize(): void
    {
        /** @var Connection $connection */
        $connection = $this->getContainer()->get(Connection::class);
        $context = Context::createDefaultContext();
        $optionId = Uuid::randomHex();
        $templateId = Uuid::randomHex();

        $this->createTemplate(
            $templateId,
            $context,
            [
                'options' => [
                    [
                        'id' => $optionId,
                        'type' => 'checkbox',
                        'typeProperties' => [],
                        'displayName' => 'Example Option',
                        'position' => \random_int(1, 10),
                    ],
                ],
            ]
        );

        $randomCheckboxOperator = $this->getOperatorIdForType($this->operatorRepository);

        // At this point, the Template and the default option should be versionized
        $versionId = $this->templateRepository->createVersion($templateId, $context);
        $versionContext = $context->createWithVersionId($versionId);

        // After this statement we should have 1 exclusion and 1 condition each with a none live version id
        $this->exclusionRepository->create(
            [
                [
                    'name' => 'exampleName',
                    'templateId' => $templateId,
                    'conditions' => [
                        [
                            'templateOptionId' => $optionId,
                            'templateExclusionOperatorId' => $randomCheckboxOperator,
                        ],
                    ],
                ],
            ],
            $versionContext
        );

        $res = $connection->fetchAssoc(
            'SELECT HEX(`version_id`) AS `version_id`, COUNT(`version_id`) AS `count` FROM `swag_customized_products_template_exclusion`;'
        );
        static::assertIsArray($res);
        static::assertNotEmpty($res);
        static::assertArrayHasKey('version_id', $res);
        static::assertArrayHasKey('count', $res);
        static::assertSame(1, (int) $res['count']);
        static::assertNotSame(Defaults::LIVE_VERSION, $res['version_id']);
        static::assertSame($versionId, \mb_strtolower($res['version_id']));

        $res = $connection->fetchAssoc(
            'SELECT HEX(`version_id`) AS `version_id`, COUNT(`version_id`) AS `count` FROM `swag_customized_products_template_exclusion_condition`;'
        );
        static::assertIsArray($res);
        static::assertNotEmpty($res);
        static::assertArrayHasKey('version_id', $res);
        static::assertArrayHasKey('count', $res);
        static::assertSame(1, (int) $res['count']);
        static::assertNotSame(Defaults::LIVE_VERSION, $res['version_id']);
        static::assertSame($versionId, \mb_strtolower($res['version_id']));

        // After merging the template all sub entities including extensions and conditons should merge too
        $this->templateRepository->merge($versionId, $versionContext);

        $res = $connection->fetchAssoc(
            'SELECT HEX(`version_id`) AS `version_id`, COUNT(`version_id`) AS `count` FROM `swag_customized_products_template_exclusion`;'
        );
        static::assertIsArray($res);
        static::assertNotEmpty($res);
        static::assertArrayHasKey('version_id', $res);
        static::assertArrayHasKey('count', $res);
        static::assertSame(1, (int) $res['count']);
        static::assertSame(Defaults::LIVE_VERSION, \mb_strtolower($res['version_id']));

        $res = $connection->fetchAssoc(
            'SELECT HEX(`version_id`) AS `version_id`, COUNT(`version_id`) AS `count` FROM `swag_customized_products_template_exclusion_condition`;'
        );
        static::assertIsArray($res);
        static::assertNotEmpty($res);
        static::assertArrayHasKey('version_id', $res);
        static::assertArrayHasKey('count', $res);
        static::assertSame(1, (int) $res['count']);
        static::assertSame(Defaults::LIVE_VERSION, \mb_strtolower($res['version_id']));
    }

    public function testConditionCanReferenceNoneMergedVersion(): void
    {
        // Create basic template
        $templateID = Uuid::randomHex();
        $context = Context::createDefaultContext();
        $this->createTemplate(
            $templateID,
            $context
        );

        // Create template version
        $versionId = $this->templateRepository->createVersion($templateID, $context);
        $versionContext = $context->createWithVersionId($versionId);

        // Create a option available under the previously created versionId
        $optionId = Uuid::randomHex();
        $this->templateRepository->update([
            [
                'id' => $templateID,
                'options' => [
                    [
                        'id' => $optionId,
                        'type' => Checkbox::NAME,
                        'displayName' => 'CanIBeAssociated? ¯\_(ツ)_/¯',
                        'position' => 1,
                        'typeProperties' => [],
                    ],
                ],
            ],
        ], $versionContext);

        // Create a exclusion with one condition referencing the created optionId
        $exclusionId = Uuid::randomHex();
        $this->exclusionRepository->create([
            [
                'id' => $exclusionId,
                'templateId' => $templateID,
                'name' => 'ShrugMe',
                'conditions' => [
                    [
                        'templateOptionId' => $optionId,
                        // TemplateExclusionConditions have a ManyToOneAssociation to TemplateOptions those will be
                        // versionized with the LiveVersionId because of that we need to specify it explicit
                        'templateOptionVersionId' => $versionId,
                        'templateExclusionOperatorId' => $this->getOperatorIdForType($this->operatorRepository),
                    ],
                ],
            ],
        ], $versionContext);

        $this->templateRepository->merge($versionId, $versionContext);
        $criteria = new Criteria([$exclusionId]);
        $criteria->addAssociation('conditions');

        /** @var TemplateExclusionEntity|null $exclusion */
        $exclusion = $this->exclusionRepository->search($criteria, $context)->first();
        static::assertNotNull($exclusion);
        $conditions = $exclusion->getConditions();
        static::assertNotNull($conditions);
        $condition = $conditions->first();
        static::assertNotNull($condition);
        static::assertSame($condition->getVersionId(), Defaults::LIVE_VERSION);
    }
}
