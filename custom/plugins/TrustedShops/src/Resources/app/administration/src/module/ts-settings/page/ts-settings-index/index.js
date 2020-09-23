import template from './ts-settings-index.html.twig';
import tsSettingsIndexState from './state';

import './ts-settings-index.scss';

const { Component, Mixin, StateDeprecated } = Shopware;
const { mapState, mapGetters } = Shopware.Component.getComponentHelper(); // Change for 6.1:
//import { mapState, mapGetters } from 'vuex'; // Old way before 6.1

Component.register('ts-settings-index', {
    template,

    mixins: [
        Mixin.getByName('notification'),
        Mixin.getByName('sw-inline-snippet')
    ],

    inject: ['systemConfigApiService'],

    metaInfo() {
        return {
            title: this.$createTitle()
        };
    },

    data() {

        return {
            namespace: "TrustedShops",
            domain: "TrustedShops.config"
        };
    },

    watch: {
        actualConfigData: {
            handler() {
                this.emitConfig();
            },
            deep: true
        }
    },

    computed: {
        currentLocale() {
            return Shopware.State.get('session').currentLocale;
        },

        ...mapState('tsSettingsIndex', [
            'config',
            'actualConfigData',
            'currentSalesChannelId',
            'salesChannelModel'
        ]),

        ...mapGetters('tsSettingsIndex', [
            'isLoading'
        ]),
    },

    beforeCreate() {
        this.$store.registerModule('tsSettingsIndex', tsSettingsIndexState);
    },

    created() {
        this.createdComponent();
    },

    beforeDestroy() {
        this.$store.unregisterModule('tsSettingsIndex');
    },

    methods: {
        createdComponent() {

            this.$store.commit('tsSettingsIndex/setLoading', true);

            this.readConfig()
                .then(() => {
                    this.readAll().then(() => {
                        this.$store.commit('tsSettingsIndex/setLoading', false);
                    });
                })
                .catch(({response: {data}}) => {
                    if (data && data.errors) {
                        this.createErrorNotification(data.errors);
                    }
                });
        },
        readConfig() {
            return this.systemConfigApiService
                .getConfig(this.domain)
                .then(data => {
                    this.$store.commit('tsSettingsIndex/setConfig', data);
                });
        },
        readAll() {
            this.$store.commit('tsSettingsIndex/setLoading', true);

            // Return when data for this salesChannel was already loaded
            if (this.actualConfigData.hasOwnProperty(this.currentSalesChannelId)) {
                this.$store.commit('tsSettingsIndex/setLoading', false);
                return Promise.resolve();
            }

            return this.systemConfigApiService.getValues(this.domain, this.currentSalesChannelId)
                .then(values => {
                    this.$set(this.actualConfigData, this.currentSalesChannelId, values);
                })
                .finally(() => {
                    this.$store.commit('tsSettingsIndex/setLoading', false);
                });
        },
        saveAll() {
            this.$store.commit('tsSettingsIndex/setLoading', true);
            return this.systemConfigApiService
                .batchSave(this.actualConfigData)
                .finally(() => {
                    this.$store.commit('tsSettingsIndex/setLoading', false);
                });
        },
        createErrorNotification(errors) {
            let message = `<div>${this.$tc(
                'sw-config-form-renderer.configLoadErrorMessage',
                errors.length
            )}</div><ul>`;

            errors.forEach((error) => {
                message = `${message}<li>${error.detail}</li>`;
            });
            message += '</ul>';

            this.createNotificationError({
                title: this.$tc('sw-config-form-renderer.configLoadErrorTitle'),
                message: message,
                autoClose: false
            });
        },
        onSalesChannelChanged(salesChannelId) {
            this.$store.commit('tsSettingsIndex/setCurrentSalesChannelId', salesChannelId);
            this.readAll();
        },
        emitConfig() {
            this.$emit('config-changed', this.actualConfigData[this.currentSalesChannelId]);
        },
        onSave() {
            this.saveAll().then(() => {
                this.createNotificationSuccess({
                    title: this.$tc('sw-plugin-config.titleSaveSuccess'),
                    message: this.$tc('sw-plugin-config.messageSaveSuccess')
                });
            }).catch((err) => {
                this.createNotificationError({
                    title: this.$tc('sw-plugin-config.titleSaveError'),
                    message: err
                });
            });
        }
    }
});
