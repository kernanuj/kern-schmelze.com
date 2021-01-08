import template from './sw-settings-index.html.twig';

const { Component } = Shopware;

// TODO: Enable once Shopware has fixed iconComponent

//const version = Shopware.Context.app.config.version;
//const match = version.match(/((\d+)\.?(\d+?)\.?(\d+)?\.?(\d*))-?([A-z]+?\d+)?/i);

// if(match && parseInt(match[2]) === 6 && parseInt(match[3]) < 2) {
Component.override('sw-settings-index', {
    template
});
// }
