import './service-point-info';
import template from './sendcloud-card.html.twig';

const { Component } = Shopware;

Component.register('sendcloud-card', {
    template,

    inject: [
        'pluginService'
    ],

    data() {
        return {
            showSendCloudCard: false,
            isLoading: true,
            orderStatus: '',
            trackingNumber: '',
            showTrackingNumber: false,
            trackingUrl: '',
            carriers: '',
            apiKey: '',
            servicePointInfo: null,
            linkLabel: this.$tc('send-cloud.shipment.selectServicePoint'),
            sendcloudScriptUrl: ''
        };
    },

    props: {
        order: {
            type: Object,
            required: true,
            default() {
                return {};
            }
        }
    },

    created: function() {
        this.fetchShipmentInfo();
    },

    methods: {
        fetchShipmentInfo: function () {
            const headers = this.pluginService.getBasicHeaders();

            return this.pluginService.httpClient
                .get('/sendcloud/shipment/' + this.order.orderNumber, {headers})
                .then((response) => {
                    let shipmentInfo = Shopware.Classes.ApiService.handleResponse(response);
                    this.isLoading = false;
                    this.orderStatus = shipmentInfo.status ? shipmentInfo.status : this.$tc('send-cloud.shipment.emptyStatusMessage');
                    this.trackingNumber = shipmentInfo.trackingNumber;
                    this.trackingUrl = shipmentInfo.trackingUrl;
                    this.showTrackingNumber = !!shipmentInfo.trackingNumber;
                    this.apiKey = shipmentInfo.apiKey;
                    this.showSendCloudCard = (this.apiKey.length > 0);
                    this.carriers = shipmentInfo.carriers;
                    this.sendcloudScriptUrl = shipmentInfo.sendcloudScriptUrl;
                    this.servicePointInfo = JSON.parse(shipmentInfo.servicePointInfo);
                    if (this.servicePointInfo.id) {
                        this.linkLabel = this.$tc('send-cloud.shipment.changeServicePoint');
                    }
                }).catch(error => {

                });
        }
    }
});