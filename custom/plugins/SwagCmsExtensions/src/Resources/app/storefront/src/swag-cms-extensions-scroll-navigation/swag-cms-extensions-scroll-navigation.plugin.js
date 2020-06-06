/* eslint-disable import/no-unresolved */
import Plugin from 'src/plugin-system/plugin.class';
import DomAccess from 'src/helper/dom-access.helper';

export default class SwagCmsExtensionsScrollNavigation extends Plugin {
    /**
     * Plugin options
     *
     * @type {Object}
     */
    static options = {
        /**
         * Selector of the list with all navigation point links
         *
         * @type {String}
         */
        navigationListSelector: '.scroll-navigation-sidebar-list',

        /**
         * Selector of the complete section, which the navigation list can navigate to.
         *
         * @type {String}
         */
        anchoredSectionsSelector: '.swag-cms-extensions-scroll-navigation-wrapper',

        /**
         * Selector of navigation list entries
         *
         * @type {String}
         */
        navigationAnchorSelector: '.scroll-navigation-anchor',

        /**
         * Selector of the entry element to which the `activeEntryClass` will be applied
         *
         * @type {String}
         */
        entrySelector: '.scroll-navigation-sidebar-entry',

        /**
         * Class name which applies to the currently active navigation list entry
         *
         * @type {String}
         */
        activeEntryClass: 'scroll-navigation-sidebar-entry--active',

        /**
         * Selector of the navigation element for mobile viewports, which shows the list of navigation points
         *
         * @type {String}
         */
        mobileListButtonSelector: '#scroll-navigation-mobile-button-list',

        /**
         * Selector of the up navigation element for mobile viewports
         *
         * @type {String}
         */
        mobileUpButtonSelector: '#scroll-navigation-mobile-button-up',

        /**
         * Selector of the down navigation element for mobile viewports
         *
         * @type {String}
         */
        mobileDownButtonSelector: '#scroll-navigation-mobile-button-down',

        /**
         * Properties of {@link https://developer.mozilla.org/en-US/docs/Web/API/IntersectionObserver}
         *
         * @type {Object}
         */
        observerOptions: {
            rootMargin: '-20% 0% -60% 0%'
        }
    };

    /**
     * Plugin constructor which filters out the necessary sections for the associated navigation entires.
     *
     * @returns {void}
     */
    init() {
        this.sidebar = this.el;
        const anchoredSections = DomAccess.querySelectorAll(document, this.options.anchoredSectionsSelector);
        this.anchoredSections = anchoredSections;
        this.navigationList = DomAccess.querySelector(this.sidebar, this.options.navigationListSelector);

        this.registerObserver(anchoredSections);
    }

    /**
     * Iterates through the provided sections and observes them using an
     * {@link https://developer.mozilla.org/en-US/docs/Web/API/IntersectionObserver}.
     *
     * @param {HtmlCollection} sectionsToObserve
     * @returns {Boolean}
     */
    registerObserver(sectionsToObserve) {
        const observer = new IntersectionObserver(this.onIntersection.bind(this), this.options.observerOptions);
        const sections = Array.from(sectionsToObserve);

        if (!sections || sections.length <= 0) {
            return false;
        }

        sections.forEach((section) => {
            observer.observe(section);
        });

        return true;
    }

    /**
     * Callback which will be fired when an element starts or stops intersecting with the root element of the observer.
     *
     * @param {Array} sections
     * @returns {Boolean}
     */
    onIntersection(sections) {
        const intersectingSection = sections.find(item => item.isIntersecting);

        if (!intersectingSection || !intersectingSection.target) {
            return false;
        }
        const target = intersectingSection.target;

        return this.setActiveNavigationItem(target);
    }

    /**
     * Removes the active class from all navigation entries and sets the provided target as the active navigation
     * entry.
     *
     * @param {DomNode} target
     * @returns {Boolean}
     */
    setActiveNavigationItem(target) {
        // De-activate all list items
        const navItems = Array.from(DomAccess.querySelectorAll(this.navigationList, this.options.entrySelector));

        if (!navItems || navItems.length <= 0) {
            return false;
        }

        navItems.forEach((item) => {
            item.classList.remove(this.options.activeEntryClass);
        });

        // Get new active item based on section id
        const navigationSection = DomAccess.querySelector(target, this.options.navigationAnchorSelector);
        const activeId = navigationSection.id;

        // Set active navigation entry
        DomAccess
            .querySelector(this.navigationList, `[href="#${activeId}"]${this.options.entrySelector}`)
            .classList.add(this.options.activeEntryClass);

        this.setUpDownNavigation(target);

        return true;
    }

    /**
     * Sets up the up and down navigation buttons for mobile viewport
     *
     * @param {DomNode} target
     * @returns {Boolean}
     */
    setUpDownNavigation(target) {
        if (this.anchoredSections.length <= 0) {
            return false;
        }

        const activeSection = DomAccess.querySelector(target, this.options.navigationAnchorSelector);
        const activeIndex = Array.from(this.anchoredSections).findIndex((section) => {
            const navigationAnchor = DomAccess.querySelector(section, this.options.navigationAnchorSelector);
            return navigationAnchor.id === activeSection.id;
        });

        if (activeIndex < 0) {
            return false;
        }

        const arrowUpButton = DomAccess.querySelector(this.sidebar, this.options.mobileUpButtonSelector);
        const arrowDownButton = DomAccess.querySelector(this.sidebar, this.options.mobileDownButtonSelector);

        const currentUrl = window.location.origin + window.location.pathname;

        this.arrowUpButtonHref = `${currentUrl}#${this.getPreviousSectionId(activeIndex)}`;
        this.arrowDownButtonHref = `${currentUrl}#${this.getNextSectionId(activeIndex)}`;
        arrowUpButton.onclick = this.onArrowUpClick.bind(this);
        arrowDownButton.onclick = this.onArrowDownClick.bind(this);

        return true;
    }

    /**
     * OnClick Event handler to navigator to the prior navigation point
     *
     * @returns {void}
     */
    onArrowUpClick() {
        window.location.href = this.arrowUpButtonHref;
    }

    /**
     * OnClick Event handler to navigator to the next navigation point
     *
     * @returns {void}
     */
    onArrowDownClick() {
        window.location.href = this.arrowDownButtonHref;
    }

    /**
     * Returns the section id of the previous section for the up button on mobile viewports
     *
     * @param {Integer} activeIndex
     * @returns {String}
     */
    getPreviousSectionId(activeIndex) {
        const targetIndex = Math.max(0, activeIndex - 1);

        return this.getSectionIdByIndex(targetIndex);
    }

    /**
     * Returns the section id of the next section for the down button on mobile viewports
     *
     * @param {Integer} activeIndex
     * @returns {String}
     */
    getNextSectionId(activeIndex) {
        const targetIndex = Math.min(this.anchoredSections.length - 1, activeIndex + 1);

        return this.getSectionIdByIndex(targetIndex);
    }

    /**
     * Helper function, which returns the section id of a section by index
     *
     * @param {Integer} targetIndex
     * @returns {String}
     */
    getSectionIdByIndex(targetIndex) {
        const section = DomAccess.querySelector(
            this.anchoredSections[targetIndex],
            this.options.navigationAnchorSelector
        );

        return section.id;
    }
}
