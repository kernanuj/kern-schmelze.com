import { ERRORS } from '../../../../../constant/swag-cms-extensions.constant';

const { Component, State, Filter } = Shopware;
const { ShopwareError } = Shopware.Classes;

Component.override('sw-cms-detail', {
    inject: [
        'repositoryFactory'
    ],

    computed: {
        scrollNavigationRepository() {
            return this.repositoryFactory.create('swag_cms_extensions_scroll_navigation');
        },

        loadPageCriteria() {
            const criteria = this.$super('loadPageCriteria');

            criteria
                .addAssociation('swagCmsExtensionsScrollNavigationPageSettings')
                .getAssociation('sections')
                .addAssociation('swagCmsExtensionsScrollNavigation')
                .getAssociation('blocks')
                .addAssociation('swagCmsExtensionsQuickview')
                .addAssociation('swagCmsExtensionsBlockRule');

            return criteria;
        }
    },

    methods: {
        onSave() {
            if (this.validateSections() === false) {
                return this.$super('onSave');
            }

            return Promise.reject();
        },

        validateSections() {
            this.occuredDisplayNames = [];
            return Array.from(this.page.sections).some(this.validateSection);
        },

        validateSection(section) {
            const currentScrollNavigation = this.getScrollNavigation(section);

            if (!currentScrollNavigation) {
                return false;
            }

            let currentName = '';
            if (currentScrollNavigation.displayName) {
                currentScrollNavigation.displayName = currentScrollNavigation.displayName.trim();
                currentName = currentScrollNavigation.displayName;
            }

            const nameTooLong = currentName.length > 255;
            if ((currentScrollNavigation.active && currentName.length === 0) || nameTooLong) {
                const code = nameTooLong ? ERRORS.SECTION.FIELD_TOO_LONG : ERRORS.SECTION.EMPTY_FIELD_WHEN_ACTIVE;

                this.commitApiError(section, 'displayName', code);
                this.onInvalidLength(section, currentName);

                return true;
            }

            if (currentScrollNavigation.active !== true) {
                return false;
            }

            if (this.occuredDisplayNames.includes(currentName)) {
                this.commitApiError(section, 'displayName', ERRORS.SECTION.DUPLICATE_VALUE);
                this.onInvalidDisplayName(section, currentName);

                return true;
            }

            // Only check active display names for duplicates
            this.occuredDisplayNames.push(currentName);

            return false;
        },

        onInvalidDisplayName(section, currentName) {
            const message = this.$tc(
                'swag-cms-extensions.sw-cms.detail.scrollNavigation.duplicateDisplayNameMessage',
                0,
                { currentName }
            );
            this.onInvalidInput(section, message);
        },

        onInvalidLength(section, currentName) {
            const truncatedName = Filter.getByName('truncate')(currentName, 40);
            const message = this.$tc(
                'swag-cms-extensions.sw-cms.detail.scrollNavigation.invalidDisplayNameLengthMessage',
                currentName.length - 1,
                { truncatedName }
            );
            this.onInvalidInput(section, message);
        },

        onInvalidInput(section, message) {
            this.selectSection(section);
            this.createNotificationError({
                title: this.$tc('global.default.error'),
                message
            });
        },

        commitApiError(section, property, code) {
            const expression = `swag_cms_extensions_scroll_navigation.${this.getScrollNavigation(section).id}.${property}`;
            const error = new ShopwareError({
                code,
                detail: this.$tc(`swag-cms-extensions.error-codes.${code}`)
            });

            State.commit('error/addApiError', { expression, error });
        },

        selectSection(section) {
            State.dispatch('cmsPageState/setSection', section);
            this.$parent.$emit('page-config-open', 'itemConfig');
        },

        getScrollNavigation(section) {
            if (section.extensions.swagCmsExtensionsScrollNavigation !== undefined) {
                return section.extensions.swagCmsExtensionsScrollNavigation;
            }
            return this.scrollNavigationRepository.create(Shopware.Context.api);
        }
    }
});
