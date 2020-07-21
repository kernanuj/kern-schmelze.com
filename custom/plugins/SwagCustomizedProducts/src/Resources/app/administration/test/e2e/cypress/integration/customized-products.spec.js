// / <reference types="Cypress" />

const selector = {
    input: {
        internalName: '#sw-field--template-internalName',
        displayName: '#sw-field--template-displayName',
        description: '.sw-text-editor__content',
        optionName: '#sw-field--newOption-displayName',
        optionType: '#sw-field--newOption-type',
        numberfield: {
            min: '#sw-field--option-typeProperties-minValue',
            max: '#sw-field--option-typeProperties-maxValue',
            step: '#sw-field--option-typeProperties-interval',
            defaultValue: '#sw-field--option-typeProperties-defaultValue'
        }
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

    it('@package @general: can navigate to custom products module', () => {
        cy.get('.swag-customized-products-list').should('exist');
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

        // Add checkbox
        cy.contains('Add option').click();
        cy.get(selector.input.optionName).type('Check this');
        cy.get(selector.input.optionType).select('Checkbox');

        cy.get(selector.button.addOption).click();
        cy.get(selector.button.applyOption).click();


        // Add an additional numberfield
        cy.contains('Add option').click();
        cy.get(selector.input.optionName).type('Number this');
        cy.get(selector.input.optionType).select('Number field');
        cy.get(selector.button.addOption).click();

        // Fill in typeProperties
        cy.get(selector.input.numberfield.min).clear().type('2');
        cy.get(selector.input.numberfield.max).clear().type('22');
        cy.get(selector.input.numberfield.step).clear().type('2');
        cy.get(selector.input.numberfield.defaultValue).clear().type('12');
        cy.get(selector.button.applyOption).click();

        // Reopen to check values
        cy.contains('.sw-data-grid__cell-content', 'Number this').click();
        cy.get(selector.input.numberfield.min).should('have.value', '2');
        cy.get(selector.input.numberfield.max).should('have.value', '22');
        cy.get(selector.input.numberfield.step).should('have.value', '2');
        cy.get(selector.input.numberfield.defaultValue).should('have.value', '12');
        cy.get(selector.button.cancelOption).click();
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
