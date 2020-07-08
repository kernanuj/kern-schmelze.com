// The e2e-testsuite-pllatform version is not compatible with 4.0.0 yet so we comment it out for now
// module.exports = require('@shopware-ag/e2e-testsuite-platform/cypress/plugins');

require('@babel/register');
const selectTestsWithGrep = require('cypress-select-tests/grep');
const semver = require('semver');

module.exports = (on, config) => {
    // `on` is used to hook into various events Cypress emits
    // `config` is the resolved Cypress config
    on('file:preprocessor', selectTestsWithGrep(config));
};
