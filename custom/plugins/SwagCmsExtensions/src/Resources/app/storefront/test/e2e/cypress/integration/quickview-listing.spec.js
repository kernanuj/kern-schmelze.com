const ProductFixture = global.ProductFixtureService;

/* global cy */
const selector = {
    productBox: {
        container: '[data-swag-cms-extensions-quickview-box]',
        name: '.product-name'
    },
    quickview: {
        headline: '.swag-cms-extensions-quickview-header-headline',
        buttonClose: '.swag-cms-extensions-quickview-close-button',
        contentContainer: '.swag-cms-extensions-quickview-container',
        buttonPrevious: '.carousel-control-prev',
        buttonNext: '.carousel-control-next',
        currentQuickview: '.carousel-item.active',
        productId: '[data-swag-cms-extensions-quickview-carousel-product-id]',
        product: {
            name: '[itemprop="name"]',
            selectQuantity: 'select.product-detail-quantity-select',
            buttonBuy: 'button.btn-buy',
            buttonDetail: 'a.btn.swag-cms-extensions-quickview-detail-page-button'
        }
    }
};

let product = {};

describe('Quickview listing', () => {
    before(() => {
        return cy.createQuickviewProductFixture().then((result) => {
            product = result;
            cy.visit('/');
        });
    });

    function openQuickview() {
        cy.get(selector.productBox.container).first().within(() => {
            cy.get(selector.productBox.name).click();
        });
    }

    it('Shows a quickview when a product name is clicked', () => {
        openQuickview();

        cy.get(selector.quickview.headline)
            .should('be.visible');

        cy.get(selector.quickview.buttonClose)
            .should('exist');

        cy.get(selector.quickview.contentContainer)
            .should('be.visible');

        cy.get(selector.quickview.buttonPrevious)
            .should('be.visible');

        cy.get(selector.quickview.buttonNext)
            .should('be.visible');

        cy.get(selector.quickview.contentContainer).within(() => {
            cy.get(selector.quickview.product.selectQuantity)
                .should('be.visible');

            cy.get(selector.quickview.product.buttonBuy)
                .should('be.visible');

            cy.get(selector.quickview.product.buttonDetail)
                .should('be.visible');
        });
    });
});
