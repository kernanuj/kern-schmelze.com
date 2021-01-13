import template from './sw-cms-sidebar-nav-element.html.twig';
import './sw-cms-sidebar-nav-element.scss';

const { Component } = Shopware;

Component.override('sw-cms-sidebar-nav-element', {
    template,

    computed: {
        hasBlockRule() {
            const rule = this.block.extensions.swagCmsExtensionsBlockRule;

            return rule && (rule.inverted || !!rule.visibilityRuleId);
        },

        ruleTooltip() {
            return {
                message: this.$tc('swag-cms-extensions.sw-cms.sidebar.navElement.blockRuleTooltip')
            };
        },

        blockRuleNavigatorClasses() {
            return {
                'has--rule': this.hasBlockRule
            };
        }
    }
});
