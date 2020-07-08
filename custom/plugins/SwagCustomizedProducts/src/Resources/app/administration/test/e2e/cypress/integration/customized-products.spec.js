// / <reference types="Cypress" />

const selector = {
    input: {
        internalName: '#sw-field--template-internalName',
        displayName: '#sw-field--template-displayName',
        description: '.sw-text-editor__content',
        optionName: '#sw-field--newOption-displayName',
        optionType: '#sw-field--newOption-type',
    },
    button: {
        addTemplate: '[href="#/swag/customized/products/create"]',
        saveTemplate: '.swag-customized-products-detail__save-action',
        addOption: '.sw-modal__footer button.sw-button--primary',
        applyOption: '.swag-customized-products-option-detail-modal .sw-modal__footer button.sw-button--primary',
        cancelOption: '.swag-customized-products-option-detail-modal .btn-cancel'
    },
    emptyState: '.swag-customized-products-detail-base__empty-state',
};

describe('Administration: Custom products', () => {
    beforeEach(() => {
        // Clean previous state and prepare Administration
        cy.loginViaApi()
            .then(() => {
                cy.setLocaleToEnGb();
            })
            .then(() => {
                cy.openInitialPage(`${Cypress.env('admin')}/#/swag/customized/products/index`);
            });
    });

    function assertListIsVisible() {
        cy.get('.swag-customized-products-list').should('exist');
    }

    function createTemplate() {
        cy.get(selector.button.addTemplate).click();
        cy.get(selector.input.internalName).type('lorem-ipsum');
        cy.get(selector.input.displayName).type('Lorem ipsum');
        cy.get(selector.input.description).type('Lorem ipsum dolor sit amet...');
        cy.get(selector.button.saveTemplate).click();
    }

    it('@package @general: can navigate to custom products module', () => {
        assertListIsVisible();
    });

    it('@package @general: can add a new template', () => {
        cy.get(selector.button.addTemplate).click();
        cy.get(selector.input.internalName).type('lorem-ipsum');
        cy.get(selector.input.displayName).type('Lorem ipsum');
        cy.get(selector.input.description).type('Lorem ipsum dolor sit amet...');
        cy.get(selector.button.saveTemplate).click();

        cy.get(selector.emptyState).should('exist');

    });

    it('@package @general: can add a new template and option', () => {
        cy.server();

        cy.get(selector.button.addTemplate).click();
        cy.get(selector.input.internalName).type('lorem-ipsum');
        cy.get(selector.input.displayName).type('Lorem ipsum');
        cy.get(selector.input.description).type('Lorem ipsum dolor sit amet...');
        cy.get(selector.button.saveTemplate).click();

        cy.get(selector.emptyState).should('not.exist');

        cy.contains('Add option').click();
        cy.get(selector.input.optionName).type('Check this');
        cy.get(selector.input.optionType).select('Checkbox');

        cy.get(selector.button.addOption).click();
        cy.get(selector.button.applyOption).click();

        cy.get(selector.emptyState).should('exist');
    });

    it('@package @general: can add a new template and try to add an option with the empty state remaining present', () => {
        cy.get(selector.button.addTemplate).click();
        cy.get(selector.input.internalName).type('lorem-ipsum');
        cy.get(selector.input.displayName).type('Lorem ipsum');
        cy.get(selector.input.description).type('Lorem ipsum dolor sit amet...');
        cy.get(selector.button.saveTemplate).click();

        cy.get(selector.emptyState).should('exist');

        cy.contains('Add option').click();
        cy.get(selector.input.optionName).type('Check this');
        cy.get(selector.input.optionType).select('Checkbox');

        cy.get(selector.button.addOption).click();
        cy.get(selector.button.cancelOption).click();

        cy.get(selector.emptyState).should('exist');
    });
});
