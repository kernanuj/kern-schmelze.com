import template from './klarna-payment-settings.html.twig';
import './klarna-payment-settings.scss';

const { Component, Mixin, Context } = Shopware;
const { Criteria } = Shopware.Data;

Component.register('klarna-payment-settings', {
    template,

    mixins: [
        Mixin.getByName('notification'),
        Mixin.getByName('sw-inline-snippet')
    ],

    inject: [
        'repositoryFactory',
        'KlarnaPaymentConfigurationService'
    ],

    data() {
        return {
            isLoading: false,
            isTesting: false,
            isTestSuccessful: false,
            isSaveSuccessful: false,
            config: {},
            paymentMethods: [],
            externalCheckoutPaymentMethods: [],
            configDomain: 'KlarnaPayment.settings.'
        };
    },

    metaInfo() {
        return {
            title: this.$createTitle()
        };
    },

    created() {
        this.createdComponent();
    },

    computed: {
        paymentMethodRepository() {
            return this.repositoryFactory.create('payment_method');
        }
    },

    methods: {
        createdComponent() {
            const me = this;

            this.paymentMethodRepository.search(new Criteria(), Context.api).then((searchResult) => {
                searchResult.forEach(((paymentMethod) => {
                    me.paymentMethods.push({
                        value: paymentMethod.id,
                        label: paymentMethod.name
                    });

                    if (paymentMethod.formattedHandlerIdentifier === 'handler_swag_paypalpaymenthandler') {
                        me.externalCheckoutPaymentMethods.push({
                            value: paymentMethod.id,
                            label: paymentMethod.name
                        });
                    }
                }));
            });
        },

        getConfigValue(field) {
            if (this.$refs.systemConfig === undefined) {
                return null;
            }

            const defaultConfig = this.$refs.systemConfig.actualConfigData.null;
            const salesChannelId = this.$refs.systemConfig.currentSalesChannelId;

            if (salesChannelId === null) {
                return this.config[this.configDomain + field];
            }

            return this.config[this.configDomain + field]
                || defaultConfig[this.configDomain + field];
        },

        onTest() {
            this.isTestSuccessful = false;
            this.isTesting = true;

            const credentials = {
                apiUsername: this.getConfigValue('apiUsername'),
                apiPassword: this.getConfigValue('apiPassword'),
                testMode: this.getConfigValue('testMode'),
                testApiUsername: this.getConfigValue('testApiUsername'),
                testApiPassword: this.getConfigValue('testApiPassword'),
                salesChannel: this.$refs.systemConfig.currentSalesChannelId
            };

            this.KlarnaPaymentConfigurationService.validateCredentials(credentials).then(() => {
                this.createNotificationSuccess({
                    title: this.$tc('klarna-payment-configuration.settingsForm.messages.titleSuccess'),
                    message: this.$tc('klarna-payment-configuration.settingsForm.messages.messageTestSuccess')
                });

                this.isTestSuccessful = true;
            }).catch((errorResponse) => {
                if (errorResponse.response.data.live) {
                    this.createNotificationError({
                        title: this.$tc('klarna-payment-configuration.settingsForm.messages.titleError'),
                        message: this.$tc('klarna-payment-configuration.settingsForm.messages.messageTestErrorLive')
                    });
                }

                if (errorResponse.response.data.test) {
                    this.createNotificationError({
                        title: this.$tc('klarna-payment-configuration.settingsForm.messages.titleError'),
                        message: this.$tc('klarna-payment-configuration.settingsForm.messages.messageTestErrorTest')
                    });
                }
            }).finally(() => {
                this.isTesting = false;
            });
        },

        onSave() {
            this.isSaveSuccessful = false;
            this.isLoading = true;

            this.$refs.systemConfig.saveAll().then(() => {
                this.createNotificationSuccess({
                    title: this.$tc('klarna-payment-configuration.settingsForm.messages.titleSuccess'),
                    message: this.$tc('klarna-payment-configuration.settingsForm.messages.messageSaveSuccess')
                });

                this.isSaveSuccessful = true;

                if (this.getConfigValue('instantShoppingEnabled')) {
                    const params = {
                        salesChannel: this.$refs.systemConfig.currentSalesChannelId
                    };

                    this.KlarnaPaymentConfigurationService.createButtonKeys(params)
                        .catch((error) => {
                            this.createNotificationError({
                                title: this.$tc('klarna-payment-configuration.settingsForm.messages.titleError'),
                                message: this.$t(error.response.data.message, error.response.data.data),
                                autoClose: false
                            });
                        });
                }
            }).catch(() => {
                this.createNotificationError({
                    title: this.$tc('klarna-payment-configuration.settingsForm.messages.titleError'),
                    message: this.$tc('klarna-payment-configuration.settingsForm.messages.messageSaveError')
                });
            }).finally(() => {
                this.isLoading = false;
            });
        },

        onConfigChange(config) {
            this.config = config;

            this.redirectToWizard();
        },

        redirectToWizard() {
            const isInitialized = this.getConfigValue('isInitialized');

            if (!isInitialized) {
                this.$router.push({ name: 'klarna.payment.configuration.wizard' });
            }
        },

        onSaveFinished() {
            this.isSaveSuccessful = false;
        },

        onTestFinished() {
            this.isTestSuccessful = false;
        },

        getBind(element, config) {
            if (config !== this.config) {
                this.config = config;
            }

            return element;
        },

        disableField(element) {
            return element.name === `${this.configDomain}klarnaType`;
        },

        onWizard() {
            this.$router.push({ name: 'klarna.payment.configuration.wizard' });
        },

        /**
         * TODO: Depending on the klarnaType (checkout or payments) fields could be filtered via their name
         */
        displayField(element, config) {
            if (element.name === `${this.configDomain}isInitialized`) {
                return false;
            }

            if (element.name === `${this.configDomain}onsiteMessagingScript`) {
                return config[`${this.configDomain}isOnsiteMessagingActive`] === true;
            }
            if (element.name === `${this.configDomain}onsiteMessagingSnippet`) {
                return config[`${this.configDomain}isOnsiteMessagingActive`] === true;
            }

            if (element.name === `${this.configDomain}captureOrderStatus`) {
                return config[`${this.configDomain}automaticCapture`] === 'orderStatus';
            }
            if (element.name === `${this.configDomain}captureDeliveryStatus`) {
                return config[`${this.configDomain}automaticCapture`] === 'deliveryStatus';
            }

            if (element.name === `${this.configDomain}refundOrderStatus`) {
                return config[`${this.configDomain}automaticRefund`] === 'orderStatus';
            }
            if (element.name === `${this.configDomain}refundDeliveryStatus`) {
                return config[`${this.configDomain}automaticRefund`] === 'deliveryStatus';
            }

            if (element.name === `${this.configDomain}newsletterCheckboxLabel`) {
                return config[`${this.configDomain}enableNewsletterCheckbox`];
            }
            if (element.name === `${this.configDomain}accountCheckboxLabel`) {
                return config[`${this.configDomain}enableAccountCheckbox`];
            }

            if (element.name === `${this.configDomain}kcoFooterBadgeStyle`) {
                return config[`${this.configDomain}kcoDisplayFooterBadge`];
            }
            if (element.name === `${this.configDomain}kcoFooterBadgeCountryCode`) {
                return config[`${this.configDomain}kcoDisplayFooterBadge`];
            }
            if (element.name === `${this.configDomain}kcoFooterBadgeWidth`) {
                return config[`${this.configDomain}kcoDisplayFooterBadge`];
            }

            if (element.name === `${this.configDomain}instantShoppingVariation`) {
                return config[`${this.configDomain}instantShoppingEnabled`];
            }
            if (element.name === `${this.configDomain}instantShoppingType`) {
                return config[`${this.configDomain}instantShoppingEnabled`];
            }
            if (element.name === `${this.configDomain}termsCategory`) {
                // TODO: also display if kco is activated
                return config[`${this.configDomain}instantShoppingEnabled`];
            }

            return true;
        },

        getOrderStatusCriteria() {
            const criteria = new Criteria(1, 100);

            criteria.addFilter(Criteria.equals('stateMachine.technicalName', 'order.state'));

            return criteria;
        },

        getDeliveryStatusCriteria() {
            const criteria = new Criteria(1, 100);

            criteria.addFilter(Criteria.equals('stateMachine.technicalName', 'order_delivery.state'));

            return criteria;
        },

        getTermsCriteria() {
            const criteria = new Criteria(1, 100);

            criteria.addFilter(Criteria.equals('visible', 1));
            criteria.addFilter(Criteria.equals('active', 1));

            return criteria;
        }
    }
});
