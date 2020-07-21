/* eslint-disable import/no-unresolved */
import Plugin from 'src/plugin-system/plugin.class';
import DomAccess from 'src/helper/dom-access.helper';
import Backdrop from 'src/utility/backdrop/backdrop.util';

export default class SwagCustomizedProductsImageZoomPlugin extends Plugin {
    /**
     * @type {{template: {}, templateSelector: string, imageSelector: string}}
     */
    static options = {
        imageSelector: '.swag-customized-products-option__image',
        templateSelector: '.swag-customized-products-image-zoom__template',
        template: {
            imageSelector: '.swag-customized-products-image-zoom__image',
            captionSelector: '.swag-customized-products-image-zoom__caption',
            closeButtonSelector: '.swag-customized-products-image-zoom__icon-close'
        }
    };

    /**
     * Plugin constructor. Finds the necessary elements from the DOM and starts the plugin.
     *
     * @constructor
     * @returns {void}
     */
    init() {
        this.template = DomAccess.querySelector(this.el, this.options.templateSelector);
        this.imageEl = DomAccess.querySelector(this.el, this.options.imageSelector);
        this.contentEl = null;
        this.closeButtonEl = null;

        this.prepareModalContent();
        this.registerEvents();
    }

    /**
     * Initializes the necessary event listeners to get the plugin working.
     *
     * @returns {void}
     */
    registerEvents() {
        this.el.addEventListener('click', this.onImageClicked.bind(this));
    }

    /**
     * Prepares the modal content. It gets the necessary information (source & alternative text) from the image
     * element within the element and updates the content inside the template, so the template can be reused.
     *
     * @fires prepareModalContent
     * @returns {boolean}
     */
    prepareModalContent() {
        const imageSrc = DomAccess.getAttribute(this.imageEl, 'src');
        const imageCaption = DomAccess.getAttribute(this.imageEl, 'alt');

        const content = this.template;
        const templateImageEl = DomAccess.querySelector(content, this.options.template.imageSelector);
        const templateCaptionEl = DomAccess.querySelector(content, this.options.template.captionSelector);

        templateImageEl.setAttribute('src', imageSrc);
        templateImageEl.setAttribute('alt', imageCaption);

        templateCaptionEl.innerText = imageCaption;

        this.$emitter.publish('prepareModalContent', {
            content,
            imageCaption,
            imageSrc
        });

        return true;
    }

    /**
     * Event listener handler which will be fired when the user clicks either on the image or the expand icon.
     *
     * @event click
     * @fires onImageClick
     * @param {Event} event
     * @returns {void}
     */
    onImageClicked(event) {
        event.preventDefault();

        this.$emitter.publish('onImageClicked', {
            event
        });

        this.spawnZoomModal();
    }

    /**
     * Event listener handler which will be fired when the user clicks on the close icon.
     *
     * @event click
     * @fires onImageClick
     * @param {Event} event
     * @returns {void}
     */
    onCloseClicked(event) {
        event.preventDefault();

        this.$emitter.publish('onCloseClicked', {
            event
        });

        Backdrop.remove();
        document.body.removeChild(this.contentEl);
    }

    /**
     * Spawns the zoom modal. It clones the template content and appends it to the body after the backdrop got
     * displayed.
     *
     * @fires spawnZoomModal
     * @returns {boolean}
     */
    spawnZoomModal() {
        this.contentEl = this.template.firstElementChild.cloneNode(true);
        this.closeButtonEl = DomAccess.querySelector(this.contentEl, this.options.template.closeButtonSelector);

        this.closeButtonEl.addEventListener('click', this.onCloseClicked.bind(this), { once: true });

        Backdrop.create(() => {
            document.body.appendChild(this.contentEl);
        });

        this.$emitter.publish('spawnZoomModal', {
            contentEl: this.contentEl
        });

        return true;
    }
}
