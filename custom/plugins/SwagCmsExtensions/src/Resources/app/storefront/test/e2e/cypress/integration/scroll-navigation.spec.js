import createId from 'uuid/v4';

const selector = {
    sidebar: {
        menu: '.scroll-navigation-sidebar',
        entryBullet: '.scroll-navigation-sidebar-entry-bullet',
        entryLabel: '.scroll-navigation-sidebar-entry-label',
    },
    mobile: {
        menuBar: '.scroll-navigation-sidebar-mobile-menu',
        menuOpenButton: '#scroll-navigation-mobile-button-list',
        menuCloseButton: '.scroll-navigation-sidebar-close',
    }
};

const className = {
    sidebar: {
        entry: 'scroll-navigation-sidebar-entry',
        entryActive: 'scroll-navigation-sidebar-entry--active',
    }
};

const color = {
    menuLabelActive: 'rgb(0, 132, 144)',
    menuLabelInactive: 'rgb(74, 84, 91)'
};

const contexts = [
    {
        deviceType: 'desktop',
        viewport: {
            // 'macbook-11' preset
            width: 1366,
            height: 768
        },
        scrollY: [2500, 5000, 7500, 0]
    },
    {
        deviceType: 'tablet',
        viewport: {
            // 'ipad-2' preset
            width: 768,
            height: 1024
        },
        scrollY: [5000, 10000, 15000, 0]
    },
    {
        deviceType: 'mobile',
        viewport:
        {
            // 'iphone-3' preset
            width: 320,
            height: 480
        },
        scrollY: [11000, 22000, 33000, 0]
    },
];

let smoothScrolling;
let categoryId = '';
let cmsPageId = '';

describe('Scroll Navigation', () => {
    before(() => {
        categoryId = createId().replace(/-/g, '');
        cmsPageId = createId().replace(/-/g, '');

        return cy.createDefaultFixture('cms-page', {
            'id': cmsPageId
        }).then((cmsPageFixture) => {
            smoothScrolling = cmsPageFixture.swagCmsExtensionsScrollNavigationPageSettings;

            return cy.createDefaultFixture('category', {
                'id': categoryId,
                'cmsPageId': cmsPageId
            });
        }).then(() => {
            return cy.searchViaAdminApi({
                endpoint: 'sales-channel',
                data: {
                    field: 'name',
                    value: 'Storefront'
                }
            });
        }).then((salesChannelSearchResult) => {
            return cy.updateViaAdminApi('sales-channel', salesChannelSearchResult.id, {
                data: {
                    navigationCategoryId: categoryId
                }
            });
        });
    });

    beforeEach(() => {
        cy.setCookie('cookie-preference', '1')
            .visit('/');
    });

    context('shows navigation menu/sidebar when visiting the landing page', () => {
        contexts.forEach((context) => {
            it(`on ${context.deviceType}`, () => {
                cy.viewport(context.viewport.width, context.viewport.height);

                cy.get(selector.sidebar.menu)
                    .should('be.visible');

                if (context.deviceType !== 'desktop') {
                    cy.get(selector.mobile.menuBar)
                        .should('be.visible');

                    cy.get(selector.mobile.menuOpenButton)
                        .should('be.visible')
                        .click();
                }

                cy.get(selector.mobile.menuBar)
                    .should('not.be.visible');

                cy.get('[href="#rose"]')
                    .should('be.visible')
                    .should('have.class', className.sidebar.entry)
                    .should('have.class', className.sidebar.entryActive);

                cy.get('[href="#beautiful-lavender"]')
                    .should('be.visible')
                    .should('have.class', className.sidebar.entry)
                    .should('not.have.class', className.sidebar.entryActive);

                cy.get('[href="#somewhat-pinkish"]')
                    .should('be.visible')
                    .should('have.class', className.sidebar.entry)
                    .should('not.have.class', className.sidebar.entryActive);

                if (context.deviceType !== 'desktop') {
                    cy.get(selector.mobile.menuCloseButton)
                        .should('be.visible')
                        .click()
                        .should('not.be.visible');

                    cy.get(selector.mobile.menuBar)
                        .should('be.visible');
                }
            });
        })
    });

    context('changes the bullets on scrolling manually', () => {
        contexts.forEach((context) => {
            it(`on ${context.deviceType}`, () => {
                cy.viewport(context.viewport.width, context.viewport.height);

                if (context.deviceType !== 'desktop') {
                    cy.get(selector.mobile.menuOpenButton)
                        .should('be.visible')
                        .click();
                }

                // Second Section without point is visible
                cy.scrollTo(0, context.scrollY[0]);

                cy.get('[href="#rose"]')
                    .should('have.class', className.sidebar.entryActive);
                cy.get('[href="#beautiful-lavender"]')
                    .should('not.have.class', className.sidebar.entryActive);
                cy.get('[href="#somewhat-pinkish"]')
                    .should('not.have.class', className.sidebar.entryActive);

                // Third Section is visible
                cy.scrollTo(0, context.scrollY[1]);

                cy.get('[href="#rose"]')
                    .should('not.have.class', className.sidebar.entryActive);
                cy.get('[href="#beautiful-lavender"]')
                    .should('have.class', className.sidebar.entryActive);
                cy.get('[href="#somewhat-pinkish"]')
                    .should('not.have.class', className.sidebar.entryActive);

                // Fourth Section is visible
                cy.scrollTo(0, context.scrollY[2]);

                cy.get('[href="#rose"]')
                    .should('not.have.class', className.sidebar.entryActive);
                cy.get('[href="#beautiful-lavender"]')
                    .should('not.have.class', className.sidebar.entryActive);
                cy.get('[href="#somewhat-pinkish"]')
                    .should('have.class', className.sidebar.entryActive);

                // First Section is, again, visible
                cy.scrollTo(0, context.scrollY[3]);

                cy.get('[href="#rose"]')
                    .should('have.class', className.sidebar.entryActive);
                cy.get('[href="#beautiful-lavender"]')
                    .should('not.have.class', className.sidebar.entryActive);
                cy.get('[href="#somewhat-pinkish"]')
                    .should('not.have.class', className.sidebar.entryActive);
            });
        });
    });
});
