import template from './sw-cms-page-form.html.twig';
import './sw-cms-page-form.scss';

const { Component } = Shopware;

Component.override('sw-cms-page-form', {
    template,

    methods: {
        getScrollNavigationBySection(section) {
            return section.extensions.swagCmsExtensionsScrollNavigation;
        },

        getScrollNavigationLabel(section) {
            const prefix = this.$tc('swag-cms-extensions.sw-cms.section.actions.pageForm.labelPrefix');
            const scrollNavigation = this.getScrollNavigationBySection(section);

            const displayName = scrollNavigation && scrollNavigation.displayName !== null ?
                scrollNavigation.displayName :
                this.$tc('swag-cms-extensions.sw-cms.section.actions.pageForm.emptyAnchorName');

            return `${prefix} - ${displayName}`;
        },

        sectionHasRulesOnAllBlocks(section) {
            return section.blocks.every((block) => {
                const rule = block.extensions.swagCmsExtensionsBlockRule;

                return rule && (rule.inverted || !!rule.visibilityRuleId);
            });
        }
    }
});
