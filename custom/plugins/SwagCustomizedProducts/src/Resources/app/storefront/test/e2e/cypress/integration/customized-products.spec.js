// / <reference types="Cypress" />

let product;
describe('Storefront: Custom products', () => {
    before(() => {
        return cy.createProductFixture().then(() => {
            return cy.createDefaultFixture('category');
        }).then(() => {
            return cy.fixture('product');
        }).then((fixtureProduct) => {
            product = fixtureProduct;
            return cy.createCustomerFixtureStorefront();
        }).then(() => {
            cy.visit('/');
        });
    });

    it('should open up the customized product in the storefront', () => {
        // Search for the created product in the storefront
        cy.get('.header-search-input')
            .should('be.visible')
            .type(product.name);
        cy.contains('.search-suggest-product-name', product.name).click();

        // Select field
        cy.contains('.swag-customized-products-option__title', 'Example select').click();
        cy.contains('.swag-customized-products-option-type-select-checkboxes-label-property', 'Example #1').click();

        // Checkbox
        cy.contains('.swag-customized-products-option__title', 'Example checkbox').click();
        cy.contains('.custom-control-label', 'Example checkbox').click();

        // Textfield
        cy.contains('.swag-customized-products-option__title', 'Example textfield').click();
        cy.get('.swag-customized-products__type-textfield input').type('Hello Customized Products Textfield');

        // Textarea
        cy.contains('.swag-customized-products-option__title', 'Example textarea').click();
        cy.get('.swag-customized-products__type-textarea textarea').type('Hello Customized Products Textarea');

        // Numberfield
        cy.contains('.swag-customized-products-option__title', 'Example numberfield').click();
        cy.get('.swag-customized-products__type-numberfield input').type('42');

        // Datefield
        cy.contains('.swag-customized-products-option__title', 'Example datefield').click();
        cy.get('.swag-customized-products__type-datetime > .input-group > input[type="text"].swag-customized-products-options-datetime').click();
        cy.get('.flatpickr-calendar').should('be.visible');
        cy.get('.flatpickr-day.today').click();

        // Time field
        cy.contains('.swag-customized-products-option__title', 'Example timefield').click();
        cy.get('.swag-customized-products__type-timestamp > .input-group > input[type="text"].swag-customized-products-options-datetime').click();
        cy.get('.flatpickr-calendar').should('be.visible');
        cy.get('.numInputWrapper .flatpickr-hour').type('3');

        // Color select
        cy.contains('.swag-customized-products-option__title', 'Example color select').click();
        cy.contains('.swag-customized-products-option-type-select-checkboxes-label-property', 'Example Purple').click();

        // Add to cart
        cy.get('.product-detail-buy .btn-buy').click();

        // Off canvas cart
        cy.get('.offcanvas.is-open').should('be.visible');
        cy.get('.cart-item-label').contains(product.name);

        // Check the configuration
        cy.contains('.swag-customized-products-cart__title-toggle', 'Configuration').click();
        cy.contains('.swag-customized-products-cart__list-bullet', 'Example #1');

        // Checkout
        cy.get('.offcanvas-cart-actions .btn-primary').click();

        // Login
        cy.get('.checkout-main').should('be.visible');
        cy.get('.login-collapse-toggle').click();
        cy.get('.login-card').should('be.visible');
        cy.get('#loginMail').typeAndCheckStorefront('test@example.com');
        cy.get('#loginPassword').typeAndCheckStorefront('shopware');
        cy.get('.login-submit [type="submit"]').click();

        // Confirm
        cy.get('.confirm-tos .card-title').contains('Terms, conditions and cancellation policy');
        cy.get('.confirm-tos .custom-checkbox label').scrollIntoView();
        cy.get('.confirm-tos .custom-checkbox label').click(1, 1);
        cy.get('.confirm-address').contains('Pep Eroni');

        // Finish checkout
        cy.get('#confirmFormSubmit').scrollIntoView();
        cy.get('#confirmFormSubmit').click();
        cy.get('.finish-header').contains('Thank you for your order with Demostore!');

        // Let's check the calculation on /finish as well
        cy.contains(product.name);
    });

    it('should configure the product using the step by step mode', () => {
        cy.fixture('step-by-step-wizard-patch').then((data) => {
            return cy.patchViaAdminApi(`swag-customized-products-template/${data.id}`, { data });
        }).then(() => {
            cy.visit('/');

            // Search for the created product in the storefront
            cy.get('.header-search-input')
                .should('be.visible')
                .type(product.name);
            cy.contains('.search-suggest-product-name', product.name).click();

            // Start wizard
            cy.get('.swag-customized-products-start-wizard.btn-primary').should('be.visible');
            cy.contains('.swag-customized-products-start-wizard.btn-primary', 'Configure product').click();

            // Select field
            cy.contains('.swag-customized-products-option__title', 'Example select').scrollIntoView();
            cy.contains('.swag-customized-products-option-type-select-checkboxes-label-property', 'Example #1').click();

            // Next button
            cy.get('.swag-customized-products-pager__button.btn-next').should('be.visible');
            cy.get('.swag-customized-products-pager__button.btn-next').click();

            // Checkbox
            cy.contains('.swag-customized-products-option__title', 'Example checkbox').scrollIntoView();
            cy.contains('.custom-control-label', 'Example checkbox').click();

            // Next button
            cy.get('.swag-customized-products-pager__button.btn-next').should('be.visible');
            cy.get('.swag-customized-products-pager__button.btn-next').click();

            // Textfield
            cy.contains('.swag-customized-products-option__title', 'Example textfield').scrollIntoView();
            cy.get('.swag-customized-products__type-textfield input').type('Hello Customized Products Textfield StepByStep');

            // Next button
            cy.get('.swag-customized-products-pager__button.btn-next').click();

            // Textarea
            cy.contains('.swag-customized-products-option__title', 'Example textarea').scrollIntoView();
            cy.get('.swag-customized-products__type-textarea textarea').type('Hello Customized Products Textarea StepByStep');

            // Next button
            cy.get('.swag-customized-products-pager__button.btn-next').should('be.visible');
            cy.get('.swag-customized-products-pager__button.btn-next').click();

            // Numberfield
            cy.contains('.swag-customized-products-option__title', 'Example numberfield').scrollIntoView();
            cy.get('.swag-customized-products__type-numberfield input').type('42');

            // Next button
            cy.get('.swag-customized-products-pager__button.btn-next').should('be.visible');
            cy.get('.swag-customized-products-pager__button.btn-next').click();

            // Datefield
            cy.contains('.swag-customized-products-option__title', 'Example datefield').scrollIntoView();
            cy.get('.swag-customized-products__type-datetime > .input-group > input[type="text"].swag-customized-products-options-datetime').click();
            cy.get('.flatpickr-calendar').should('be.visible');
            cy.get('.flatpickr-day.today').click();

            // We have to wait here to update the pager, the flatpickr is kinda weird in this regard
            cy.wait(50);

            // Next button
            cy.get('.swag-customized-products-pager__button.btn-next').should('be.visible');
            cy.get('.swag-customized-products-pager__button.btn-next').click();

            // Time field
            cy.contains('.swag-customized-products-option__title', 'Example timefield').scrollIntoView();
            cy.get('.swag-customized-products__type-timestamp > .input-group > input[type="text"].swag-customized-products-options-datetime').click();
            cy.get('.flatpickr-calendar').should('be.visible');
            cy.get('.numInputWrapper .flatpickr-hour').type('3{enter}');

            // We have to wait here to update the pager, the flatpickr is kinda weird in this regard
            cy.wait(50);

            // Next button
            cy.get('.swag-customized-products-pager__button.btn-next').should('be.visible');
            cy.get('.swag-customized-products-pager__button.btn-next').click();

            // Color select
            cy.contains('.swag-customized-products-option__title', 'Example color select').scrollIntoView();
            cy.contains('.swag-customized-products-option-type-select-checkboxes-label-property', 'Example Purple').click();

            // Next button
            cy.get('.swag-customized-products-pager__button.btn-next').should('be.visible');
            cy.get('.swag-customized-products-pager__button.btn-next').click();

            // Check if the configuration was done
            cy.contains('.swag-customized-products-start-wizard', 'Change configuration').should('be.visible');

            // Add to cart
            cy.get('.product-detail-buy .btn-buy').click();

            // Off canvas cart
            cy.get('.offcanvas.is-open').should('be.visible');
            cy.get('.cart-item-label').contains(product.name);

            // Check the configuration
            cy.contains('.swag-customized-products-cart__title-toggle', 'Configuration').click();
            cy.contains('.swag-customized-products-cart__list-bullet', 'Example #1');

            // Checkout
            cy.get('.offcanvas-cart-actions .btn-primary').click();

            // Login
            cy.get('.checkout-main').should('be.visible');
            cy.get('.login-collapse-toggle').click();
            cy.get('.login-card').should('be.visible');
            cy.get('#loginMail').typeAndCheckStorefront('test@example.com');
            cy.get('#loginPassword').typeAndCheckStorefront('shopware');
            cy.get('.login-submit [type="submit"]').click();

            // Confirm
            cy.get('.confirm-tos .card-title').contains('Terms, conditions and cancellation policy');
            cy.get('.confirm-tos .custom-checkbox label').scrollIntoView();
            cy.get('.confirm-tos .custom-checkbox label').click(1, 1);
            cy.get('.confirm-address').contains('Pep Eroni');

            // Finish checkout
            cy.get('#confirmFormSubmit').scrollIntoView();
            cy.get('#confirmFormSubmit').click();
            cy.get('.finish-header').contains('Thank you for your order with Demostore!');

            // Let's check the calculation on /finish as well
            cy.contains(product.name);
        });
    });

    it('should be able to access the configuration from account -> orders', () => {
        cy.visit('/account/order');

        // Login
        cy.get('#loginMail').typeAndCheckStorefront('test@example.com');
        cy.get('#loginPassword').typeAndCheckStorefront('shopware');
        cy.get('.login-submit [type="submit"]').click();

        // Show first order
        cy.get('.order-table:nth-child(1) .order-item-actions > .order-hide-btn').should('be.visible');
        cy.get('.order-table:nth-child(1) .order-item-actions > .order-hide-btn').click();

        // Open configuration
        cy.get('.order-table:nth-child(1) .swag-customized-products-cart__title-toggle').click();

        // Select field
        cy.contains('.order-table:nth-child(1) .swag-customized-products-cart__items > .swag-customized-products-cart__item:nth-child(1) .swag-customized-products-cart__name', 'Example select');
        cy.contains('.order-table:nth-child(1) .swag-customized-products-cart__items > .swag-customized-products-cart__item:nth-child(1) .swag-customized-products-cart__item-content .swag-customized-products-cart__list-bullet', 'Example #1');

        // Checkbox
        cy.contains('.order-table:nth-child(1) .swag-customized-products-cart__items > .swag-customized-products-cart__item:nth-child(2) .swag-customized-products-cart__name', 'Example checkbox');
        cy.contains('.order-table:nth-child(1) .swag-customized-products-cart__items > .swag-customized-products-cart__item:nth-child(2) .swag-customized-products-cart__item-content .swag-customized-products-cart__list-bullet', 'Example checkbox');

        // Text field
        cy.contains('.order-table:nth-child(1) .swag-customized-products-cart__items > .swag-customized-products-cart__item:nth-child(3) .swag-customized-products-cart__name', 'Example textfield');
        cy.contains('.order-table:nth-child(1) .swag-customized-products-cart__items > .swag-customized-products-cart__item:nth-child(3) .swag-customized-products-cart__item-content .swag-customized-products-cart__list-bullet', 'Hello Customized Products Textfield StepByStep');

        // Text area
        cy.contains('.order-table:nth-child(1) .swag-customized-products-cart__items > .swag-customized-products-cart__item:nth-child(4) .swag-customized-products-cart__name', 'Example textarea');
        cy.contains('.order-table:nth-child(1) .swag-customized-products-cart__items > .swag-customized-products-cart__item:nth-child(4) .swag-customized-products-cart__item-content .swag-customized-products-cart__list-bullet', 'Hello Customized Products Textarea StepByStep');

        // Show second order
        cy.get('.order-table:nth-child(2) .order-item-actions > .order-hide-btn').should('be.visible');
        cy.get('.order-table:nth-child(2) .order-item-actions > .order-hide-btn').click();

        // Open configuration
        cy.get('.order-table:nth-child(2) .swag-customized-products-cart__title-toggle').click();

        // Select field
        cy.contains('.order-table:nth-child(2) .swag-customized-products-cart__items > .swag-customized-products-cart__item:nth-child(1) .swag-customized-products-cart__name', 'Example select');
        cy.contains('.order-table:nth-child(2) .swag-customized-products-cart__items > .swag-customized-products-cart__item:nth-child(1) .swag-customized-products-cart__item-content .swag-customized-products-cart__list-bullet', 'Example #1');

        // Checkbox
        cy.contains('.order-table:nth-child(2) .swag-customized-products-cart__items > .swag-customized-products-cart__item:nth-child(2) .swag-customized-products-cart__name', 'Example checkbox');
        cy.contains('.order-table:nth-child(2) .swag-customized-products-cart__items > .swag-customized-products-cart__item:nth-child(2) .swag-customized-products-cart__item-content .swag-customized-products-cart__list-bullet', 'Example checkbox');

        // Text field
        cy.contains('.order-table:nth-child(2) .swag-customized-products-cart__items > .swag-customized-products-cart__item:nth-child(3) .swag-customized-products-cart__name', 'Example textfield');
        cy.contains('.order-table:nth-child(2) .swag-customized-products-cart__items > .swag-customized-products-cart__item:nth-child(3) .swag-customized-products-cart__item-content .swag-customized-products-cart__list-bullet', 'Hello Customized Products Textfield');

        // Text area
        cy.contains('.order-table:nth-child(2) .swag-customized-products-cart__items > .swag-customized-products-cart__item:nth-child(4) .swag-customized-products-cart__name', 'Example textarea');
        cy.contains('.order-table:nth-child(2) .swag-customized-products-cart__items > .swag-customized-products-cart__item:nth-child(4) .swag-customized-products-cart__item-content .swag-customized-products-cart__list-bullet', 'Hello Customized Products Textarea');
    });
});
