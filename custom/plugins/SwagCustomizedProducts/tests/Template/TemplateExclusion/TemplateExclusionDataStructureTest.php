<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Test\Template\TemplateExclusion;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Content\Rule\RuleDefinition;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Uuid\Uuid;
use Swag\CustomizedProducts\Template\Aggregate\TemplateExclusion\TemplateExclusionDefinition;
use Swag\CustomizedProducts\Template\TemplateDefinition;
use Swag\CustomizedProducts\Template\TemplateEntity;
use Swag\CustomizedProducts\Test\Helper\ServicesTrait;

class TemplateExclusionDataStructureTest extends TestCase
{
    use ServicesTrait;

    private const REPOSITORY_POSTFIX = '.repository';

    /**
     * @var EntityRepositoryInterface
     */
    private $templateRepository;

    /**
     * @var EntityRepositoryInterface
     */
    private $templateExclusionRepository;

    /**
     * @var EntityRepositoryInterface
     */
    private $ruleRepository;

    protected function setUp(): void
    {
        $container = $this->getContainer();
        $this->templateRepository = $container->get(TemplateDefinition::ENTITY_NAME . self::REPOSITORY_POSTFIX);
        $this->templateExclusionRepository = $container->get(TemplateExclusionDefinition::ENTITY_NAME . self::REPOSITORY_POSTFIX);
        $this->ruleRepository = $container->get(RuleDefinition::ENTITY_NAME . self::REPOSITORY_POSTFIX);
    }

    public function testCreateTemplateWithExclusion(): void
    {
        $context = Context::createDefaultContext();

        $this->createTemplate(
            Uuid::randomHex(),
            $context,
            [
                'exclusions' => [
                    [
                        'name' => 'firstEverTemplateExclusion',
                    ],
                ],
            ]
        );
        $this->assertExclusionCount(1, $context);
    }

    public function testReadingTemplateWithExclusionAssociationIncludesRuleEntity(): void
    {
        $context = Context::createDefaultContext();
        $templateId = Uuid::randomHex();

        $this->createTemplate(
            $templateId,
            $context,
            [
                'exclusions' => [
                    [
                        'name' => 'firstEverTemplateExclusion',
                    ],
                ],
            ]
        );
        $this->assertExclusionCount(1, $context);

        $criteria = new Criteria([$templateId]);
        $criteria->addAssociation('exclusions');

        /** @var TemplateEntity|null $template */
        $template = $this->templateRepository->search($criteria, $context)->get($templateId);
        static::assertNotNull($template);

        $exclusionCollection = $template->getExclusions();
        static::assertNotNull($exclusionCollection);
        static::assertCount(1, $exclusionCollection);

        $exclusion = $exclusionCollection->first();
        static::assertNotNull($exclusion);
    }

    private function assertExclusionCount(int $expectedCount, Context $context): void
    {
        $ids = $this->templateExclusionRepository->searchIds(new Criteria(), $context)->getIds();
        if ($expectedCount <= 0) {
            static::assertEmpty($ids);

            return;
        }

        static::assertNotEmpty($ids);
        static::assertCount($expectedCount, $ids);
    }
}
