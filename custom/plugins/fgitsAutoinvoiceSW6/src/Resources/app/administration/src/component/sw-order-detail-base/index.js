import './sw-order-detail-base.scss';
import template from './sw-order-detail-base.html.twig';

const {Component} = Shopware;

Component.override('sw-order-detail-base', {
    template
});
