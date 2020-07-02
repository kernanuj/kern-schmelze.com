import template from './sw-social-shopping-channel-network-facebook.html.twig';

const { Component } = Shopware;
const { Criteria } = Shopware.Data;

Component.extend('sw-social-shopping-channel-network-facebook', 'sw-social-shopping-channel-network-base', {
    template,

    inject: [
        'repositoryFactory'
    ],

    data() {
        return {
            productComparisonAccessUrl: '',
            hasProductComparisonAccessUrl: false,
            socialShoppingSalesChannelDomain: null,
            socialShoppingSalesChannelDomainLoading: false,
            socialShoppingSalesChannelExport: null,
            socialShoppingSalesChannelExportLoading: false,
            disableGenerateByCronjob: false
        };
    },

    computed: {
        getIntervalOptions() {
            const intervals = [0, 120, 300, 600, 900, 1800, 3600, 7200, 14400, 28800, 43200, 86400, 172800, 259200, 345600, 432000, 518400, 604800];
            return intervals.map((value) => ({
                id: value,
                name: this.$tc(`sw-sales-channel.detail.productComparison.intervalLabels.${value}`)
            }));
        },

        salesChannelDomainRepository() {
            return this.repositoryFactory.create('sales_channel_domain');
        },

        productExportRepository() {
            return this.repositoryFactory.create('product_export');
        }
    },

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            this.$super('createdComponent');

            if (this.isNewEntity) {
                this.salesChannel.extensions.socialShoppingSalesChannel.configuration.interval = 86400;
                this.salesChannel.extensions.socialShoppingSalesChannel.configuration.generateByCronjob = true;
                this.salesChannel.extensions.socialShoppingSalesChannel.configuration.includeVariants = true;

                return;
            }

            this.generateAccessUrl();
        },

        onGoogleProductCategoryChanged(value) {
            let parsedValue = parseInt(value, 10);

            if (parsedValue <= 0 || Number.isNaN(parsedValue)) {
                parsedValue = null;
            }

            this.salesChannel.extensions.socialShoppingSalesChannel.configuration.defaultGoogleProductCategory = parsedValue;
            this.$forceUpdate();
        },

        generateAccessUrl() {
            if (!this.socialShoppingSalesChannelDomain && !this.socialShoppingSalesChannelDomainLoading) {
                this.loadSalesChannelDomain(this.salesChannel.extensions.socialShoppingSalesChannel.salesChannelDomainId);
            }

            if (!this.socialShoppingSalesChannelExport && !this.socialShoppingSalesChannelExportLoading) {
                this.loadSalesChannelExport(this.salesChannel.extensions.socialShoppingSalesChannel.salesChannelId);
            }

            if (!this.socialShoppingSalesChannelDomain || !this.socialShoppingSalesChannelExport) {
                return;
            }

            const salesChannelDomainUrl = this.socialShoppingSalesChannelDomain.url.replace(/\/+$/g, '');
            const accessKey = this.socialShoppingSalesChannelExport.accessKey;
            const fileName = this.socialShoppingSalesChannelExport.fileName;
            this.hasProductComparisonAccessUrl = true;
            this.productComparisonAccessUrl =
                `${salesChannelDomainUrl}/export/${accessKey}/${fileName}`;
        },

        loadSalesChannelDomain(salesChannelDomainId) {
            const criteria = new Criteria();
            criteria.setIds([salesChannelDomainId]);

            this.socialShoppingSalesChannelDomainLoading = true;

            this.salesChannelDomainRepository.search(criteria, Shopware.Context.api).then((result) => {
                this.socialShoppingSalesChannelDomain = result.get(salesChannelDomainId);
                this.socialShoppingSalesChannelDomainLoading = false;

                this.generateAccessUrl();
            });
        },

        loadSalesChannelExport(salesChannelId) {
            const criteria = new Criteria();
            criteria.addFilter(Criteria.equals('salesChannelId', salesChannelId));

            this.socialShoppingSalesChannelExportLoading = true;

            this.productExportRepository.search(criteria, Shopware.Context.api).then((result) => {
                this.socialShoppingSalesChannelExport = result.first();
                this.socialShoppingSalesChannelExportLoading = false;

                this.generateAccessUrl();
            });
        },

        changeInterval() {
            const socialShoppingSalesChannel = this.salesChannel.extensions.socialShoppingSalesChannel;
            this.disableGenerateByCronjob = socialShoppingSalesChannel.configuration.interval === 0;

            if (this.disableGenerateByCronjob) {
                this.salesChannel.extensions.socialShoppingSalesChannel.configuration.generateByCronjob = false;
            }
        }

    }
});
