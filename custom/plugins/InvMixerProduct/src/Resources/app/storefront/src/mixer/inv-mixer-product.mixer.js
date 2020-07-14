import Plugin from 'src/plugin-system/plugin.class';
import HttpClient from 'src/service/http-client.service'

//import DomAccess from 'src/plugin-system/dom-access.helper';
export default class InvMixerProductMixer extends Plugin {

    static options = {
        /**
         * @type string
         */
        urlMixState: '/produkt-mixer/mix/state'
    };

    init() {
        this._client = new HttpClient()
        this.attachListingEvents()
        this.attachMixStateEvents()
        this.loadState()
    }

    /**
     *
     * @param form Element
     */
    performActionByForm(form) {
        if (form.tagName !== 'FORM') {
            return
        }
        const url = form.getAttribute('action');
        this._client.post(url, new FormData(form), (response) => {
            this.displayState(response)
        })
    }

    attachMixStateEvents() {
        const listingProducts = document.querySelectorAll('[data-inv-mixer-mix-state-action]');
        for (const i in listingProducts) {
            if (listingProducts.hasOwnProperty(i)) {
                listingProducts[i].addEventListener('submit', event => {
                    this.performActionByForm(event.target)
                    event.preventDefault()
                })
            }
        }
    }

    attachListingEvents() {
        const listingProducts = document.querySelectorAll('[data-inv-mixer-product-listing-action]');
        for (const i in listingProducts) {
            if (listingProducts.hasOwnProperty(i)) {
                listingProducts[i].addEventListener('submit', event => {
                    this.performActionByForm(event.target)
                    event.preventDefault()
                })
            }
        }
    }

    displayState(stateInnerHtml) {
        this.el.innerHTML = stateInnerHtml;
        const displayContainer = document.getElementById('mix-state-container');
        const isComplete = displayContainer.dataset.mixStateIsComplete || 0;
        const isFilled = displayContainer.dataset.mixStateIsFilled || 0;

        try {
            if (isComplete) {
                document.querySelector('#mix-state-add-to-cart-anchor').scrollIntoView({
                    behavior: 'smooth'
                });
            }

            if (isFilled) {
                document.querySelector('#mix-state-set-label-anchor').scrollIntoView({
                    behavior: 'smooth'
                });
            }
        }catch (e) {
            //ignore
        }

        this.attachMixStateEvents()
    }

    loadState() {
        const that = this;
        this._client.get(that.options.urlMixState, content => this.displayState(content));
    }
}
