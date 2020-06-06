import template from './sw-sales-channel-detail.html.twig';

const { Component } = Shopware;

Component.override('sw-sales-channel-detail', {
    template,

    inject: [
        'socialShoppingService'
    ],

    data() {
        return {
            isNewEntity: false,
            networkClasses: null,
            networkName: 'base'
        };
    },

    watch: {
        salesChannel() {
            if (this.isSocialShopping && !this.salesChannel.extensions.socialShoppingSalesChannel.configuration) {
                this.salesChannel.extensions.socialShoppingSalesChannel.configuration = {};
                this.setNetworkName();
            }

            this.$forceUpdate();
        },

        networkName() {
            this.$forceUpdate();
        },

        isSocialShopping() {
            this.$forceUpdate();
        }
    },

    computed: {
        isSocialShopping() {
            return this.salesChannel && this.salesChannel.typeId.indexOf('9ce0868f406d47d98cfe4b281e62f098') !== -1;
        },

        socialShoppingType() {
            if (!this.salesChannel
                || !this.salesChannel.extensions
                || !this.salesChannel.extensions.socialShoppingSalesChannel
                || !this.networkClasses
            ) {
                return '';
            }

            return `sw-social-shopping-channel-network-${this.getNetworkByFQCN(
                this.salesChannel.extensions.socialShoppingSalesChannel.network
            )}`;
        }
    },

    methods: {
        createdComponent() {
            this.$super('createdComponent');

            this.socialShoppingService.getNetworks().then((networks) => {
                this.networkClasses = networks;
                this.setNetworkName();
            });
        },

        getNetworkByFQCN(fqcn) {
            return Object.keys(this.networkClasses).filter((key) => { return this.networkClasses[key] === fqcn; })[0];
        },

        setNetworkName() {
            if (!this.salesChannel
                || !this.salesChannel.extensions
                || !this.salesChannel.extensions.socialShoppingSalesChannel
                || !this.networkClasses
            ) {
                return;
            }

            this.networkName = this.getNetworkByFQCN(this.salesChannel.extensions.socialShoppingSalesChannel.network);
        }
    }

});
