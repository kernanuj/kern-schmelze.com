const { Component } = Shopware;

Component.register('sw-social-shopping-channel-integration-step', {

    props: {
        steps: {
            type: String,
            require: true
        }
    },

    render(h) {
        return h(
            'ol',
            {
                class: 'sw-sales-channel-integration-card-step-by-step-list',
                domProps: {
                    innerHTML: this.steps
                }
            }
        );
    }

});
