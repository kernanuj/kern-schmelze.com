import template from './ts-settings-index-start.html.twig';

const { Component, StateDeprecated } = Shopware;
const { mapState, mapGetters } = Shopware.Component.getComponentHelper(); // Change for 6.1:
//import { mapState, mapGetters } from 'vuex'; // Old way before 6.1

Component.register('ts-settings-index-start', {
    template,

    inject: [
        'pluginService'
    ],

    data() {
        return {
            plugin: {}
        }
    },

    created() {
        this.createdComponent();
    },

    computed: {
        ...mapState('tsSettingsIndex', [
            'config',
            'actualConfigData',
            'currentSalesChannelId',
            'salesChannelModel'
        ]),

        ...mapGetters('tsSettingsIndex', [
            'isLoading'
        ]),

        pluginsStore() {
            return StateDeprecated.getStore('plugin');
        }
    },

    methods: {
        createdComponent() {
            this.loadPluginVersion();
        },

        loadPluginVersion() {
            this.pluginService.refresh().then(() => {
                return this.pluginsStore.getList(
                    {},
                    false,
                    Shopware.State.get('session').languageId
                );
            }).then((response) => {
                const plugins = response.items;

                if( plugins.length ) {
                    plugins.forEach((plugin) => {
                        if( plugin.name === 'TrustedShops' ) {
                            this.plugin = plugin;
                        }
                    });
                }
            });
        }

    }
});
