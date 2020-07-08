import template from './swag-customized-products-media-media-item.html.twig';

const { Component } = Shopware;

Component.extend('swag-customized-products-media-media-item', 'sw-media-media-item', {
    template,

    methods: {
        /**
         * Downloads a file via url.
         *
         * @param {String} url
         */
        onDownload(url) {
            const el = document.createElement('a');
            el.setAttribute('download', 'download');
            el.setAttribute('rel', 'noopener');
            el.setAttribute('target', '_blank');
            el.setAttribute('href', url);
            el.style.display = 'hidden';

            document.body.appendChild(el);

            el.click();
            this.$nextTick(() => {
                el.parentNode.removeChild(el);
            });
        }
    }
});
