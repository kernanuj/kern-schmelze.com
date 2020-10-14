export default class InvMixerProductStateRepresentation {

    mixState;

    constructor(mixStateAsJson) {
        if (typeof mixStateAsJson != 'object') {
            throw 'needs a json object';
        }
        this.mixState = mixStateAsJson;
    }

    static fromString(mixStateAsString) {
        return new InvMixerProductStateRepresentation(
            $.parseJSON(mixStateAsString)
        );
    }

    /**
     * @param productId string
     * @return boolean
     */
    isProductIdContainedInMix(productId) {
        let isContained = false;
        this.mixState.items.forEach(item => {
           if(item.productId === productId){
               isContained = true;
           }
        })
        return isContained;
    }

    /**
     * @param productId string
     * @return number
     */
    getQuantityOfProductContainedInMix(productId) {
        let quantity = 0;
        this.mixState.items.forEach(item => {
            if(item.productId === productId){
                quantity = item.quantity;
            }
        })
        return quantity;
    }

    applyStateToListingButtonsInForm(form) {
        let self = this;
        $(form).find('button.inv-mixerProduct-button-animated-loading').each(
            function(index){
                let productId = $(this).data('invMixerProductListingProductId');
                if(self.isProductIdContainedInMix(productId)) {
                    let stateContainedTextElement = $(this).find('.label-state-contained').first();
                    let originalText = stateContainedTextElement.data('textOriginal');
                    stateContainedTextElement.text(
                        originalText.replace(
                            '{{count}}',
                            self.getQuantityOfProductContainedInMix(productId)
                        )
                    )
                    $(this)
                        .addClass('state-contained')
                        .removeClass('state-initial')
                        .removeClass('state-result-success')
                        .removeClass('state-result-failure')
                        .removeClass('state-progress')
                } else {
                    $(this)
                        .addClass('state-initial')
                        .removeClass('state-result-contained')
                        .removeClass('state-result-success')
                        .removeClass('state-result-failure')
                        .removeClass('state-progress');
                }
            }
        )
    }
}
