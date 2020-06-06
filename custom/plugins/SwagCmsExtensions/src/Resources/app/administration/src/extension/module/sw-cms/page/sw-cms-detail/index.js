const { Component, State, Filter } = Shopware;

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

            criteria.getAssociation('sections')
                .addAssociation('swagCmsExtensionsScrollNavigation')
                .getAssociation('blocks')
                .addAssociation('swagCmsExtensionsQuickview');

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

            if ((currentScrollNavigation.active && currentName.length === 0) || currentName.length > 255) {
                this.onInvalidLength(section, currentName);

                return true;
            }

            if (currentScrollNavigation.active !== true) {
                return false;
            }

            if (this.occuredDisplayNames.includes(currentName)) {
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
