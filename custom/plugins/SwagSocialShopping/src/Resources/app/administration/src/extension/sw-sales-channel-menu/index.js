const { Component } = Shopware;

Component.override('sw-sales-channel-menu', {

    methods: {
        createMenuTree() {
            this.$super('createMenuTree');

            const networkIconMapping = this.getNetworkIconMapping();
            const iconById = {};
            this.salesChannels.forEach((salesChannel) => {
                if (salesChannel.extensions.socialShoppingSalesChannel !== undefined) {
                    iconById[salesChannel.id] = networkIconMapping[
                        salesChannel.extensions.socialShoppingSalesChannel.network
                    ];
                }
            });

            this.menuItems.forEach((menuItem) => {
                if (iconById[menuItem.id] !== undefined) {
                    menuItem.icon = iconById[menuItem.id];
                }
            });
        },

        getNetworkIconMapping() {
            return {
                'SwagSocialShopping\\Component\\Network\\Facebook': 'brand-facebook',
                'SwagSocialShopping\\Component\\Network\\GoogleShopping': 'brand-google',
                'SwagSocialShopping\\Component\\Network\\Pinterest': 'brand-pinterest',
                'SwagSocialShopping\\Component\\Network\\Instagram': 'brand-instagram'
            };
        }
    }
});
