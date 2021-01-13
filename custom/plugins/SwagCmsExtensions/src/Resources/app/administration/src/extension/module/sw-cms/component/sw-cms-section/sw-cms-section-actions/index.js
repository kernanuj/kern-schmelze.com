import template from './sw-cms-section-actions.html.twig';
import './sw-cms-section-actions.scss';

const { Component } = Shopware;

Component.override('sw-cms-section-actions', {
    template,

    computed: {
        scrollNavigation() {
            return this.section.extensions.swagCmsExtensionsScrollNavigation;
        },

        hasRulesOnAllBlocks() {
            return this.section.blocks.every((block) => {
                const rule = block.extensions.swagCmsExtensionsBlockRule;

                return rule && (rule.inverted || !!rule.visibilityRuleId);
            });
        },

        scrollNavigationPointTooltip() {
            const tooltipPrefix = this.$tc('swag-cms-extensions.sw-cms.section.actions.viewports.tooltipPrefix');
            const displayName = this.scrollNavigation && this.scrollNavigation.displayName ?
                this.scrollNavigation.displayName :
                this.$tc('swag-cms-extensions.sw-cms.section.actions.viewports.emptyAnchorName');

            return {
                message: `${tooltipPrefix} ${displayName}`,
                position: 'right'
            };
        },

        rulesOnAllBlocksTooltip() {
            return {
                message: this.$tc('swag-cms-extensions.sw-cms.sidebar.rulesOnAllBlocks'),
                position: 'right'
            };
        }
    }
});
