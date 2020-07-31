export default class InvMixerProductAnimation {

    static buttonAnimationStartInForm(form) {
        try {
            if (form.tagName !== 'FORM') {
                return
            }
            $(form).find('button.inv-mixerProduct-button-animated-loading')
                .removeClass('state-initial')
                .removeClass('state-result-success')
                .removeClass('state-result-failure')
                .addClass('state-progress')
        } catch (e) {
            //ignore
        }
    }

    static buttonAnimationResultInForm(form, isFailure) {
        try {
            if (form.tagName !== 'FORM') {
                return
            }

            const self = this
            if(isFailure) {
                $(form).find('button.inv-mixerProduct-button-animated-loading')
                    .removeClass('state-initial')
                    .removeClass('state-result-success')
                    .addClass('state-result-failure')
                    .removeClass('state-progress')
            }else {
                $(form).find('button.inv-mixerProduct-button-animated-loading')
                    .removeClass('state-initial')
                    .addClass('state-result-success')
                    .removeClass('state-result-failure')
                    .removeClass('state-progress')
            }
            window.setTimeout(
                function () {
                    self.buttonAnimationStopInForm(form)
                },
                3000
            )
        } catch (e) {
            //ignore
        }
    }

    static buttonAnimationStopInForm(form) {
        try {
            if (form.tagName !== 'FORM') {
                return
            }
            $(form).find('button.inv-mixerProduct-button-animated-loading')
                .addClass('state-initial')
                .removeClass('state-result-success')
                .removeClass('state-result-failure')
                .removeClass('state-progress')
        } catch (e) {
            //ignore
        }
    }
}
