import template from './ts-settings-start-banner.html.twig';
import './ts-settings-start-banner.scss';

const { Component } = Shopware;

Component.register( 'ts-settings-start-banner', {
    template,

    props: {
        pluginVersion: {
            type: String,
            required: false,
            default: ''
        }
    }
});