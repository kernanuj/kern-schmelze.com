import template from './ts-settings-index-shopreviews.html.twig';

const { Component } = Shopware;
const { mapState, mapGetters } = Shopware.Component.getComponentHelper(); // Change for 6.1:
//import { mapState, mapGetters } from 'vuex'; // Old way before 6.1

Component.register('ts-settings-index-shopreviews', {
    template,

    computed: {
        ...mapState('tsSettingsIndex', [
            'config',
            'actualConfigData',
            'currentSalesChannelId',
            'salesChannelModel'
        ]),

        ...mapGetters('tsSettingsIndex', [
            'isLoading'
        ])
    }


});
