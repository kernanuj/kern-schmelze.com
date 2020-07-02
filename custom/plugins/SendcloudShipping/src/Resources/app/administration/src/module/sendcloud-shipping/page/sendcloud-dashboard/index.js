import template from './sendcloud-dashboard.html.twig';
import '../../component/sendcloud-notification';

const { Component } = Shopware;

Component.register('sendcloud-dashboard', {
    template,

    inject: [
        'pluginService'
    ],

    data() {
        return {
            isLoading: true,
            isServicePointEnabled: false,
            salesChannel: '',
            sendcloudUrl: ''
        };
    },

    created: function() {
        this.getDashboardConfig();
    },

    methods: {
        getDashboardConfig: function() {
            const headers = this.pluginService.getBasicHeaders();

            return this.pluginService.httpClient
                .get('/sendcloud/dashboard', {headers})
                .then((response) => {
                    this.isLoading = false;
                    let configData = Shopware.Classes.ApiService.handleResponse(response);
                    this.isServicePointEnabled = configData.isServicePointEnabled;
                    this.salesChannel = configData.salesChannel;
                    this.sendcloudUrl = configData.sendcloudUrl;
                }).catch(error => {

                });
        },

        goToSendCloud: function () {
            var win = window.open(this.sendcloudUrl, '_blank');
            win.focus();
        },
    }
});