const { Component } = Shopware;

Component.override('sw-cms-detail', {
    methods: {
        cloneSlotsInBlock(block, newBlock) {
            const blockRuleRepository = this.repositoryFactory.create('swag_cms_extensions_block_rule');
            const blockRule = block.extensions.swagCmsExtensionsBlockRule;
            const newBlockRule = blockRuleRepository.create();

            newBlockRule.cmsBlockId = blockRule.cmsBlockId;
            newBlockRule.inverted = blockRule.inverted;
            newBlockRule.visibilityRuleId = blockRule.visibilityRuleId;

            newBlock.extensions.swagCmsExtensionsBlockRule = newBlockRule;

            this.$super('cloneSlotsInBlock', block, newBlock);
        }
    }
});
