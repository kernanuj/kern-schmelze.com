import template from './klarna-payment-settings.html.twig';
import './klarna-payment-settings.scss';

const { Component, Mixin } = Shopware;
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
            externalCheckoutPaymentMethods: []
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

            this.paymentMethodRepository.search(new Criteria(), Shopware.Context.api).then((searchResult) => {
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
            const defaultConfig = this.$refs.systemConfig.actualConfigData.null;
            const salesChannelId = this.$refs.systemConfig.currentSalesChannelId;

            if (salesChannelId === null) {
                return this.config[`KlarnaPayment.settings.${field}`];
            }

            return this.config[`KlarnaPayment.settings.${field}`]
                || defaultConfig[`KlarnaPayment.settings.${field}`];
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
            }).catch(() => {
                this.createNotificationSuccess({
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

        disableField(element, config) {
            if (element.name === 'KlarnaPayment.settings.klarnaType') {
                return true;
            }

            return false;
        },

        onWizard() {
            this.$router.push({ name: 'klarna.payment.configuration.wizard' });
        },

        /**
         * TODO: Depending on the klarnaType (checkout or payments) fields could be filtered via their name
         */
        displayField(element, config) {
            if (element.name === 'KlarnaPayment.settings.isInitialized') {
                return false;
            }

            if (element.name === 'KlarnaPayment.settings.onsiteMessagingScript') {
                return config['KlarnaPayment.settings.isOnsiteMessagingActive'] === true;
            }
            if (element.name === 'KlarnaPayment.settings.onsiteMessagingSnippet') {
                return config['KlarnaPayment.settings.isOnsiteMessagingActive'] === true;
            }

            if (element.name === 'KlarnaPayment.settings.captureOrderStatus') {
                return config['KlarnaPayment.settings.automaticCapture'] === 'orderStatus';
            }
            if (element.name === 'KlarnaPayment.settings.captureDeliveryStatus') {
                return config['KlarnaPayment.settings.automaticCapture'] === 'deliveryStatus';
            }

            if (element.name === 'KlarnaPayment.settings.refundOrderStatus') {
                return config['KlarnaPayment.settings.automaticRefund'] === 'orderStatus';
            }
            if (element.name === 'KlarnaPayment.settings.refundDeliveryStatus') {
                return config['KlarnaPayment.settings.automaticRefund'] === 'deliveryStatus';
            }

            if (element.name === 'KlarnaPayment.settings.newsletterCheckboxLabel') {
                return config['KlarnaPayment.settings.enableNewsletterCheckbox'];
            }
            if (element.name === 'KlarnaPayment.settings.accountCheckboxLabel') {
                return config['KlarnaPayment.settings.enableAccountCheckbox'];
            }

            if (element.name === 'KlarnaPayment.settings.kcoFooterBadgeStyle') {
                return config['KlarnaPayment.settings.kcoDisplayFooterBadge'];
            }
            if (element.name === 'KlarnaPayment.settings.kcoFooterBadgeCountryCode') {
                return config['KlarnaPayment.settings.kcoDisplayFooterBadge'];
            }
            if (element.name === 'KlarnaPayment.settings.kcoFooterBadgeWidth') {
                return config['KlarnaPayment.settings.kcoDisplayFooterBadge'];
            }

            return true;
        },

        getOrderStatusCriteria() {
            const criteria = new Criteria(1, 100);

            criteria.addFilter(
                Criteria.equals(
                    'stateMachine.technicalName',
                    'order.state'
                )
            );

            return criteria;
        },

        getDeliveryStatusCriteria() {
            const criteria = new Criteria(1, 100);

            criteria.addFilter(
                Criteria.equals(
                    'stateMachine.technicalName',
                    'order_delivery.state'
                )
            );

            return criteria;
        }
    }
});
