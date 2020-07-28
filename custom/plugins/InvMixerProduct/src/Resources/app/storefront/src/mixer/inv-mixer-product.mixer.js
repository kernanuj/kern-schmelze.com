import Plugin from 'src/plugin-system/plugin.class';
import HttpClient from 'src/service/http-client.service'

//import DomAccess from 'src/plugin-system/dom-access.helper';
export default class InvMixerProductMixer extends Plugin {

    static options = {
        /**
         * @type string
         */
        urlMixState: '/produkt-mixer/mix/state',
        urlMixStateMobile: '/produkt-mixer/mix/state?view=mobile'
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
            this.updateStateMobile()
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

    //attachMixStateEventsMobile(displayContainer) {
    attachMixStateEventsMobile() {
        /*const listingProducts = displayContainer.querySelectorAll('[data-inv-mixer-mix-state-action]');
        for (const i in listingProducts) {

            if (listingProducts.hasOwnProperty(i)) {
                listingProducts[i].addEventListener('submit', event => {
                    this.performActionByForm(event.target)
                    event.preventDefault()
                })
            }
        }*/
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
            if(this.el.getElementsByClassName('flashbags .alert').length > 0){
                console.log('yes.');
                document.querySelector('#mix-state-container .flashbags .alert').scrollIntoView({
                    behavior: 'smooth'
                });
            }
            else if (isComplete) {
                document.querySelector('#mix-state-add-to-cart-anchor').scrollIntoView({
                    behavior: 'smooth'
                });
            }
            else if (isFilled) {
                document.querySelector('#mix-state-set-label-anchor').scrollIntoView({
                    behavior: 'smooth'
                });
            }
        }catch (e) {
            //ignore
        }

        $('#mixer-product-off-canvas-botton').on('click', function(e){
            e.preventDefault();
            e.stopPropagation();
            var offcanvas_id =  $(this).attr('data-trigger');
            $(offcanvas_id).addClass('minimal');
        });

        $('.mix-product-offcanvas-close, .screen-overlay').click(function(e){
            $('.screen-overlay').removeClass('show');
            $('#mixer-product-offcanvas').toggleClass('minimal');
            e.preventDefault()
        });

        this.attachMixStateEvents()
    }

    //displayStateMobile(stateInnerHtml) {
    displayStateMobile() {
        //const displayContainer = document.getElementById('inv-mixer-product-mobile-enhancer-container');
        //displayContainer.innerHTML = stateInnerHtml;
        //this.attachMixStateEventsMobile(displayContainer)
        this.attachMixStateEventsMobile()
    }

    loadState() {
        const that = this;
        this._client.get(that.options.urlMixState, content => this.displayState(content));
        this._client.get(that.options.urlMixStateMobile, content => this.displayStateMobile(content));
    }

    updateStateMobile() {
        const that = this;
        this._client.get(that.options.urlMixStateMobile, content => this.displayStateMobile(content));
    }
}
