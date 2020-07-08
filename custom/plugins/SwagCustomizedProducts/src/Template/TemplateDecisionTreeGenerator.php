<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Template;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Swag\CustomizedProducts\Template\Aggregate\TemplateExclusion\TemplateExclusionCollection;
use Swag\CustomizedProducts\Template\Aggregate\TemplateExclusionCondition\TemplateExclusionConditionEntity;

class TemplateDecisionTreeGenerator implements TemplateDecisionTreeGeneratorInterface
{
    /**
     * @var EntityRepositoryInterface
     */
    private $templateRepository;

    /**
     * @var EntityRepositoryInterface
     */
    private $templateExclusionRepository;

    public function __construct(
        EntityRepositoryInterface $templateRepository,
        EntityRepositoryInterface $templateExclusionRepository
    ) {
        $this->templateRepository = $templateRepository;
        $this->templateExclusionRepository = $templateExclusionRepository;
    }

    public function generate(string $templateId, Context $context): array
    {
        $exclusions = $this->getExclusions($templateId, $context);

        $tree = [];
        foreach ($exclusions as $exclusion) {
            $conditions = $exclusion->getConditions();
            if ($conditions === null) {
                continue;
            }

            $exclusionData = [];
            foreach ($conditions as $condition) {
                $exclusionData[] = $this->getTreeLeaf($condition);
            }

            $tree[] = $exclusionData;
        }

        $this->templateRepository->update([
            [
                'id' => $templateId,
                'decisionTree' => $tree,
            ],
        ], $context);

        return $tree;
    }

    private function getExclusions(string $templateId, Context $context): TemplateExclusionCollection
    {
        $criteria = new Criteria();
        $criteria->addAssociation('conditions');
        $criteria->getAssociation('conditions')
            ->addAssociation('templateExclusionOperator')
            ->addAssociation('templateOptionValues')
            ->addAssociation('templateOption.values');
        $criteria->addFilter(
            new EqualsFilter('templateId', $templateId)
        );

        /** @var TemplateExclusionCollection $collection */
        $collection = $this->templateExclusionRepository->search($criteria, $context)->getEntities();

        return $collection;
    }

    private function getTreeLeaf(TemplateExclusionConditionEntity $condition): array
    {
        $templateOption = $condition->getTemplateOption();
        $operator = $condition->getTemplateExclusionOperator()->getOperator();

        return [
            'id' => $condition->getTemplateOptionId(),
            'type' => $templateOption->getType(),
            'operator' => [
                'type' => $operator,
            ],
        ];
    }
}
