import template from './sw-cms-section-config.html.twig';

const { Component } = Shopware;

Component.override('sw-cms-section-config', {
    template,

    computed: {
        hasRulesOnAllBlocks() {
            return this.section.blocks.every((block) => {
                const rule = block.extensions.swagCmsExtensionsBlockRule;

                return rule && (rule.inverted || !!rule.visibilityRuleId);
            });
        }
    }
});
