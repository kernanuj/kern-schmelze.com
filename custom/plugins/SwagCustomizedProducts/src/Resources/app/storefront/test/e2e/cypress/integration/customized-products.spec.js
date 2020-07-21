// / <reference types="Cypress" />

const waitingTimeForNextButton = 400;
const waitingTimeForFlatpickr = 300;

let product;
describe('Storefront: Custom products', () => {
    before(() => {
        return cy.createDefaultFixture('category').then(() => {
            // Read the content of the product.json
            return cy.fixture('product')
        }).then((fixtureProduct) => {
            product = fixtureProduct;
            // Now fetch the tax based on name
            return cy.searchViaAdminApi({
                endpoint: 'tax',
                data: {
                    field: 'name',
                    value: 'Standard rate'
                }
            })
        }).then((tax) => {
            // Add the tax id to the options and option values
            product.swagCustomizedProductsTemplate.options = product.swagCustomizedProductsTemplate.options.map((value) => {
                value.taxId = tax.id;
                if (!Object.prototype.hasOwnProperty.call(value, 'values')) {
                    return value;
                }
                value.values = value.values.map((item) => {
                    item.taxId = tax.id;
                    return item;
                });
                return value;
            });

            // Create the product
            return cy.createProductFixture(product);
        }).then(() => {
            // Create a default customer
            return cy.createCustomerFixtureStorefront();
        }).then(() => {
            // ...last but not least, visit the storefront
            cy.visit('/');
        });
    });

    it('should not break the account overview with no orders', () => {
        // Login
        cy.get('#accountWidget')
            .should('be.visible')
            .click();
        cy.get('[href="/account"]')
            .should('be.visible')
            .click();
        cy.get('#loginMail').typeAndCheckStorefront('test@example.com');
        cy.get('#loginPassword').typeAndCheckStorefront('shopware');
        cy.get('.login-submit [type="submit"]').click();

        // Check for basic information
        cy.contains('.account-overview-profile p', 'Mr. Pep Eroni');
        cy.contains('.account-overview-profile p ~ p', 'test@example.com');

        // No latest order yet
        cy.get('.account-overview-card account-overview-newest-order').should('not.exist');
    });

    it('should open up the customized product in the storefront', () => {
        // Search for the created product in the storefront
        cy.get('.header-search-input')
            .should('be.visible')
            .type(product.name);
        cy.contains('.search-suggest-product-name', product.name).click();

        // Check for the price box
        cy.get('.swag-customized-product__price-display').should('not.exist');
        cy.get('.swag-customized-product__price-display').should('be.exist');

        // Check for the product price
        cy.contains('.price-display__product-price > .price-display__label', 'Product price');
        cy.contains('.price-display__product-price > .price-display__price', '€10.00*');

        // Check the total price
        cy.contains('.price-display__total-price > .price-display__price', '€10.00*');

        // Select field (required)
        cy.contains('.swag-customized-products-option__title', 'Example select').should('be.visible');
        cy.contains('.swag-customized-products-option-type-select-checkboxes-label-property', 'Example #1')
            .should('be.visible')
            .click();

        // Check for the price box
        cy.get('.swag-customized-product__price-display').should('be.exist');

        // Check unit price
        cy.contains('.list__unit-price .price-display__item:nth-child(2) > .price-display__label', 'Example #1');
        cy.contains('.list__unit-price .price-display__item:nth-child(2) > .price-display__price', '€10.00*');

        // Check one time price
        cy.contains('.list__one-time-price .price-display__item .price-display__label', 'Example select');
        cy.contains('.list__one-time-price .price-display__item .price-display__price', '€10.00*');

        // Total price
        cy.contains('.price-display__total-price > .price-display__price', '€30.00*');

        // Checkbox
        cy.contains('.custom-control-label', 'Example checkbox').should('not.be.visible');
        cy.contains('.swag-customized-products-option__title', 'Example checkbox')
            .should('be.visible')
            .click();
        cy.contains('.custom-control-label', 'Example checkbox')
            .should('be.visible')
            .click();

        // Check collapsing of prior option, after changing input of the current
        cy.get('.swag-customized-products-option-type-select-checkboxes-label-property')
            .should('not.be.visible');

        // Check price display
        cy.get('.swag-customized-product__price-display').should('be.exist');
        cy.contains('.list__one-time-price .price-display__item:nth-child(2) .price-display__label', 'Example checkbox');
        cy.contains('.list__one-time-price .price-display__item:nth-child(2) .price-display__price', '€10.00*');

        // Total price
        cy.contains('.price-display__total-price > .price-display__price', '€40.00*');

        // Textfield (required)
        cy.contains('.swag-customized-products-option__title', 'Example textfield')
            .should('be.visible');
        cy.get('.swag-customized-products__type-textfield input')
            .should('be.visible')
            .type('Hello Customized Products Textfield{enter}');

        // Check collapsing of prior option, after changing input of the current
        cy.contains('.custom-control-label', 'Example checkbox').should('not.be.visible');

        // Check price display
        cy.get('.swag-customized-product__price-display').should('be.exist');
        cy.contains('.list__one-time-price .price-display__item:nth-child(3) .price-display__label', 'Example textfield');
        cy.contains('.list__one-time-price .price-display__item:nth-child(3) .price-display__price', '€10.00*');

        // Total price
        cy.contains('.price-display__total-price > .price-display__price', '€50.00*');

        // Textarea
        cy.get('.swag-customized-products__type-textarea textarea').should('not.be.visible');
        cy.contains('.swag-customized-products-option__title', 'Example textarea').click();
        cy.get('.swag-customized-products__type-textarea textarea')
            .should('be.visible')
            .type('Hello Customized Products Textarea');
        cy.contains('.swag-customized-products-option__title', 'Example textarea').click();

        // Check collapsing of prior option, after changing input of the current
        cy.get('.swag-customized-products__type-textfield input')
            .should('not.be.visible');

        // Check price display
        cy.get('.swag-customized-product__price-display').should('be.exist');
        cy.contains('.list__one-time-price .price-display__item:nth-child(4) .price-display__label', 'Example textarea');
        cy.contains('.list__one-time-price .price-display__item:nth-child(4) .price-display__price', '€10.00*');

        // Total price
        cy.contains('.price-display__total-price > .price-display__price', '€60.00*');

        // Numberfield (required)
        cy.contains('.swag-customized-products-option__title', 'Example numberfield').should('be.visible');
        cy.get('.swag-customized-products__type-numberfield input')
            .should('be.visible')
            .type('42');
        cy.contains('.swag-customized-products-option__title', 'Example numberfield').click();

        // Check collapsing of prior option, after changing input of the current
        cy.get('.swag-customized-products__type-textarea textarea').should('not.be.visible');

        // Price display
        cy.get('.swag-customized-product__price-display').should('be.exist');
        cy.contains('.list__one-time-price .price-display__item:nth-child(5) .price-display__label', 'Example numberfield');
        cy.contains('.list__one-time-price .price-display__item:nth-child(5) .price-display__price', '€10.00*');

        // Total price
        cy.contains('.price-display__total-price > .price-display__price', '€70.00*');

        // Datefield
        cy.get('.swag-customized-products__type-datetime > .input-group > input[type="text"].swag-customized-products-options-datetime')
            .should('not.be.visible');
        cy.contains('.swag-customized-products-option__title', 'Example datefield')
            .should('be.visible')
            .click();
        cy.get('.swag-customized-products__type-datetime > .input-group > input[type="text"].swag-customized-products-options-datetime')
            .should('be.visible')
            .click();
        cy.get('.flatpickr-calendar').should('be.visible');
        cy.get('.flatpickr-day.today').click();

        // Check collapsing of prior option, after changing input of the current
        cy.get('.swag-customized-products__type-numberfield input').should('not.be.visible');

        // Price display
        cy.get('.swag-customized-product__price-display').should('be.exist');
        cy.contains('.list__one-time-price .price-display__item:nth-child(6) .price-display__label', 'Example datefield');
        cy.contains('.list__one-time-price .price-display__item:nth-child(6) .price-display__price', '€10.00*');

        // Total price
        cy.contains('.price-display__total-price > .price-display__price', '€80.00*');

        // Time field
        cy.get('.swag-customized-products__type-timestamp > .input-group > input[type="text"].swag-customized-products-options-datetime')
            .should('not.be.visible');
        cy.contains('.swag-customized-products-option__title', 'Example timefield')
            .should('be.visible')
            .click();
        cy.get('.swag-customized-products__type-timestamp > .input-group > input[type="text"].swag-customized-products-options-datetime')
            .should('be.visible')
            .click();
        cy.get('.flatpickr-calendar').should('be.visible');
        cy.get('.numInputWrapper .flatpickr-hour').type('3');

        // Check collapsing of prior option, after changing input of the current
        cy.get('.swag-customized-products__type-datetime > .input-group > input[type="text"].swag-customized-products-options-datetime')
            .should('not.be.visible');

        // Price display
        cy.get('.swag-customized-product__price-display').should('be.exist');
        cy.contains('.list__one-time-price .price-display__item:nth-child(7) .price-display__label', 'Example timefield');
        cy.contains('.list__one-time-price .price-display__item:nth-child(7) .price-display__price', '€10.00*');

        // Total price
        cy.contains('.price-display__total-price > .price-display__price', '€90.00*');

        // Color select
        cy.contains('.swag-customized-products-option-type-select-checkboxes-label-property', 'Example Purple')
            .should('not.be.visible');
        cy.contains('.swag-customized-products-option__title', 'Example color select')
            .should('be.visible')
            .click();
        cy.contains('.swag-customized-products-option-type-select-checkboxes-label-property', 'Example Purple')
            .should('be.visible')
            .click();

        // Check collapsing of prior option, after changing input of the current
        cy.get('.swag-customized-products__type-timestamp > .input-group > input[type="text"].swag-customized-products-options-datetime')
            .should('not.be.visible');

        // Price display
        cy.get('.swag-customized-product__price-display').should('be.exist');
        cy.contains('.list__one-time-price .price-display__item:nth-child(8) .price-display__label', 'Example color select');
        cy.contains('.list__one-time-price .price-display__item:nth-child(8) .price-display__price', '€10.00*');

        cy.contains('.list__unit-price .price-display__item:nth-child(3) > .price-display__label', 'Example Purple');
        cy.contains('.list__unit-price .price-display__item:nth-child(3) > .price-display__price', '€10.00*');

        // Total price
        cy.contains('.price-display__total-price > .price-display__price', '€110.00*').should('be.visible');

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
            cy.wait(waitingTimeForNextButton);
            cy.get('.swag-customized-products-pager__button.btn-next').click();

            // Checkbox
            cy.contains('.swag-customized-products-option__title', 'Example checkbox').scrollIntoView();
            cy.contains('.custom-control-label', 'Example checkbox').click();

            // Next button
            cy.get('.swag-customized-products-pager__button.btn-next').should('be.visible');
            cy.wait(waitingTimeForNextButton);
            cy.get('.swag-customized-products-pager__button.btn-next').click();

            // Textfield
            cy.contains('.swag-customized-products-option__title', 'Example textfield').scrollIntoView();
            cy.get('.swag-customized-products__type-textfield input').type('Hello Customized Products Textfield StepByStep');

            // Next button
            cy.get('.swag-customized-products-pager__button.btn-next').should('be.visible');
            cy.wait(waitingTimeForNextButton);
            cy.get('.swag-customized-products-pager__button.btn-next').click();

            // Textarea
            cy.contains('.swag-customized-products-option__title', 'Example textarea').scrollIntoView();
            cy.get('.swag-customized-products__type-textarea textarea').type('Hello Customized Products Textarea StepByStep');

            // Next button
            cy.get('.swag-customized-products-pager__button.btn-next').should('be.visible');
            cy.wait(waitingTimeForNextButton);
            cy.get('.swag-customized-products-pager__button.btn-next').click();

            // Numberfield
            cy.contains('.swag-customized-products-option__title', 'Example numberfield').scrollIntoView();
            cy.get('.swag-customized-products__type-numberfield input').type('42');

            // Next button
            cy.get('.swag-customized-products-pager__button.btn-next').should('be.visible');
            cy.wait(waitingTimeForNextButton);
            cy.get('.swag-customized-products-pager__button.btn-next').click();

            // Datefield
            cy.contains('.swag-customized-products-option__title', 'Example datefield').scrollIntoView();
            cy.get('.swag-customized-products__type-datetime > .input-group > input[type="text"].swag-customized-products-options-datetime').click();
            cy.get('.flatpickr-calendar').should('be.visible');
            cy.get('.flatpickr-day.today').click();

            // We have to wait here to update the pager, the flatpickr is kinda weird in this regard
            cy.wait(waitingTimeForFlatpickr);

            // Next button
            cy.get('.swag-customized-products-pager__button.btn-next').should('be.visible');
            cy.wait(waitingTimeForNextButton);
            cy.get('.swag-customized-products-pager__button.btn-next').click();

            // Time field
            cy.contains('.swag-customized-products-option__title', 'Example timefield').scrollIntoView();
            cy.get('.swag-customized-products__type-timestamp > .input-group > input[type="text"].swag-customized-products-options-datetime').click();
            cy.get('.flatpickr-calendar').should('be.visible');
            cy.get('.numInputWrapper .flatpickr-hour').type('3{enter}');

            // We have to wait here to update the pager, the flatpickr is kinda weird in this regard
            cy.wait(waitingTimeForFlatpickr);

            // Next button
            cy.get('.swag-customized-products-pager__button.btn-next').should('be.visible');
            cy.wait(waitingTimeForNextButton);
            cy.get('.swag-customized-products-pager__button.btn-next').click();

            // Color select
            cy.contains('.swag-customized-products-option__title', 'Example color select').scrollIntoView();
            cy.contains('.swag-customized-products-option-type-select-checkboxes-label-property', 'Example Purple').click();

            // Next button
            cy.get('.swag-customized-products-pager__button.btn-next').should('be.visible');
            cy.wait(waitingTimeForNextButton);
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
