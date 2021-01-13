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
        this.getCurrentRoute({});
    },

    watch: {
        $route(to, from) {
            let query = {};

            if (to.hasOwnProperty('query') && Object.keys(to.query).length > 0) {
                query = to.query;
            } else if (from.hasOwnProperty('query') && Object.keys(from.query).length > 0) {
                query = from.query;
            }

            this.getCurrentRoute(query);
        }
    },

    methods: {
        getCurrentRoute: function (query) {
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
                        query: query
                    };

                    this.$router.replace(route);
                }).catch(error => {

                });
        }
    }
});
