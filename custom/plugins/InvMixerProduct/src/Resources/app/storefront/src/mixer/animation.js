export default class InvMixerProductAnimation {

    static buttonAnimationStartInForm(form) {
        try {
            if (form.tagName !== 'FORM') {
                return
            }
            $(form).find('button.inv-mixerProduct-button-animated-loading')
                .removeClass('state-initial')
                .removeClass('state-contained')
                .removeClass('state-result-success')
                .removeClass('state-result-failure')
                .addClass('state-progress')
        } catch (e) {
            //ignore
        }
    }

    /**
     *
     * @param form
     * @param isFailure
     * @param mixStateRepresentation InvMixerProductStateRepresentation
     */
    static buttonAnimationResultInForm(form, isFailure, mixStateRepresentation) {
        try {
            if (form.tagName !== 'FORM') {
                return
            }

            const self = this
            if(isFailure) {
                $(form).find('button.inv-mixerProduct-button-animated-loading')
                    .removeClass('state-initial')
                    .removeClass('state-result-contained')
                    .removeClass('state-result-success')
                    .addClass('state-result-failure')
                    .removeClass('state-progress')
            }else {
                $(form).find('button.inv-mixerProduct-button-animated-loading')
                    .removeClass('state-initial')
                    .removeClass('state-result-contained')
                    .addClass('state-result-success')
                    .removeClass('state-result-failure')
                    .removeClass('state-progress')
            }
            window.setTimeout(
                function () {
                    self.buttonAnimationStopInForm(form, mixStateRepresentation)
                },
                3000
            )
        } catch (e) {
            //ignore
        }
    }

    /**
     *
     * @param form
     * @param mixStateRepresentation InvMixerProductStateRepresentation
     */
    static buttonAnimationStopInForm(form, mixStateRepresentation) {
        try {
            if (form.tagName !== 'FORM') {
                return
            }
            this.applyStateToListingButtons(form, mixStateRepresentation);

        } catch (e) {
            //ignore;
        }
    }

    static applyStateToListingButtons(form, mixStateRepresentation) {
        mixStateRepresentation.applyStateToListingButtonsInForm(form);
    }
}
