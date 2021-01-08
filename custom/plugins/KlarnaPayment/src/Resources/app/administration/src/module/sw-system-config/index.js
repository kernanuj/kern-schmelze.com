const { Component } = Shopware;

Component.override('sw-system-config', {
    methods: {
        onSalesChannelChanged(salesChannelId) {
            this.$super('onSalesChannelChanged', salesChannelId);

            this.$emit('saleschannel-changed', this.currentSalesChannelId);
        },
    },
    computed: {
        typesWithMapInheritanceSupport() {
            let types = this.$super('typesWithMapInheritanceSupport');
            if (this.domain === 'KlarnaPayment.settings') {
                types.push('single-select');
                types.push('multi-select');
            }
            return types;
        }
    }
});

