import template from './sendcloud-welcome.html.twig';

const { Component } = Shopware;

Component.register('sendcloud-welcome', {
    template,

    inject: [
        'pluginService'
    ],

    data() {
        return {
            isLoading: false,
        };
    },

    methods: {
        startAuthProcess: function () {
            let headers = this.pluginService.getBasicHeaders();

            return this.pluginService.httpClient
                .get('/sendcloud/redirectUrl', {headers})
                .then((response) => {
                    let apiResponse = Shopware.Classes.ApiService.handleResponse(response);
                    this.redirectToConnectionScreenAndStartChecking(apiResponse.redirectUrl);
                }).catch(error => {
                    console.log(error);
                });
        },

        redirectToConnectionScreenAndStartChecking: function (redirectUrl) {
            this.isLoading = true;
            var win = window.open(redirectUrl, '_blank');
            win.focus();
            this.checkStatus();
        },

        checkStatus: function () {
            let headers = this.pluginService.getBasicHeaders();

            this.pluginService.httpClient
                .get('/sendcloud/connectionStatus', {headers})
                .then((response) => {
                    let apiResponse = Shopware.Classes.ApiService.handleResponse(response);
                    if (apiResponse.isConnected) {
                        window.location.reload();
                    } else {
                        var handler = this.checkStatus;
                        setTimeout(handler, 250);
                    }
                }).catch(error => {
                console.log(error);
            });
        }
    }
});