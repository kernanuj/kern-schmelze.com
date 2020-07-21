/* eslint-disable */

// Polyfill from: https://gist.github.com/nuxodin/73a2c2423cbbf6818c28ad803985d5c7#gistcomment-2942209
if (!HTMLFormElement.prototype.reportValidity) {
    HTMLFormElement.prototype.reportValidity = function () {
        if (this.checkValidity()) {
            return true;
        }
        const btn = document.createElement('button');
        this.appendChild(btn);
        btn.click();
        this.removeChild(btn);

        return false;
    };
}

if (!HTMLInputElement.prototype.reportValidity) {
    HTMLInputElement.prototype.reportValidity = function () {
        if (this.checkValidity()) {
            return true;
        }

        let tmpForm;
        if (!this.form) {
            tmpForm = document.createElement('form');
            tmpForm.style.display = 'inline';
            this.before(tmpForm);
            tmpForm.append(this);
        }
        const siblings = Array.from(this.form.elements).filter(function (input) {
            return input !== this && !!input.checkValidity && !input.disabled;
        }, this);

        siblings.forEach(function (input) {
            input.disabled = true;
        });

        this.form.reportValidity();
        siblings.forEach(function (input) {
            input.disabled = false;
        });

        if (tmpForm) {
            tmpForm.before(this);
            tmpForm.remove();
        }

        this.focus();
        this.selectionStart = 0;
        return false;
    };
}
