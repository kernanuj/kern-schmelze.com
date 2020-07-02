import template from './sendcloud-index.html.twig';

const { Component } = Shopware;

Component.register('sendcloud-index', {
    template,

    inject: [
        'pluginService'
    ],
    data() {
        return {
            isLoading: true
        };
    },

    mounted: function () {
        this.getCurrentRoute();
    },

    watch: {
        $route(to, from) {
            this.getCurrentRoute();
        }
    },

    methods: {
        getCurrentRoute: function () {
            const headers = this.pluginService.getBasicHeaders();

            return this.pluginService.httpClient
                .get('/sendcloud/router', {headers})
                .then((response) => {
                    this.isLoading = false;
                    let routeName = Shopware.Classes.ApiService.handleResponse(response).page;
                    let route = {
                        name: 'sendcloud.shipping.index',
                        params: {
                            page: routeName
                        },
                    };
                    this.$router.replace(route);
                }).catch(error => {

                });
        }
    }
});
