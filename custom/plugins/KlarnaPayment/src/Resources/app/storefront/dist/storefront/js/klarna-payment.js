(window.webpackJsonp=window.webpackJsonp||[]).push([["klarna-payment"],{Refz:function(e,t,n){"use strict";n.r(t);var a=n("FGIj"),r=n("3xtq"),o=n("u0Tz"),i=n("gHbT");function l(e){return(l="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(e){return typeof e}:function(e){return e&&"function"==typeof Symbol&&e.constructor===Symbol&&e!==Symbol.prototype?"symbol":typeof e})(e)}function u(e,t){for(var n=0;n<t.length;n++){var a=t[n];a.enumerable=a.enumerable||!1,a.configurable=!0,"value"in a&&(a.writable=!0),Object.defineProperty(e,a.key,a)}}function m(e,t){return!t||"object"!==l(t)&&"function"!=typeof t?function(e){if(void 0===e)throw new ReferenceError("this hasn't been initialised - super() hasn't been called");return e}(e):t}function c(e){return(c=Object.setPrototypeOf?Object.getPrototypeOf:function(e){return e.__proto__||Object.getPrototypeOf(e)})(e)}function d(e,t){return(d=Object.setPrototypeOf||function(e,t){return e.__proto__=t,e})(e,t)}var s=function(e){function t(){return function(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}(this,t),m(this,c(t).apply(this,arguments))}var n,a,l;return function(e,t){if("function"!=typeof t&&null!==t)throw new TypeError("Super expression must either be null or a function");e.prototype=Object.create(t&&t.prototype,{constructor:{value:e,writable:!0,configurable:!0}}),t&&d(e,t)}(t,e),n=t,(a=[{key:"init",value:function(){this._showElement("klarnaConfirmFormSubmit");var e=document.getElementById("klarna-configuration");e&&(this.clientToken=e.getAttribute("data-client-token"),this.paymentCategory=e.getAttribute("data-klarna-code"),this.customerData=JSON.parse(e.getAttribute("data-customer-data")),this.paymentCategory&&this._disableSubmitButton(),this._createScript())}},{key:"_createScript",value:function(){var e=document.createElement("script");e.type="text/javascript",e.src="https://x.klarnacdn.net/kp/lib/v1/api.js",e.addEventListener("load",this._handleScriptLoaded.bind(this),!1),document.head.appendChild(e)}},{key:"_handleScriptLoaded",value:function(){try{Klarna.Payments.init({client_token:this.clientToken})}catch(e){return this._hideElement("klarnaPaymentsContainer"),this._showElement("klarnaUnavailableError"),void this._disableSubmitButton()}var e=this;if(this.paymentCategory){var t=i.a.querySelector(document,".klarna-payment");o.a.create(t),e._hideElement("klarnaPaymentsContainer"),e._emptyElement("klarnaPaymentsContainer"),e._disableSubmitButton();try{Klarna.Payments.load({container:"#klarnaPaymentsContainer",payment_method_category:this.paymentCategory},(function(n){n.show_form?(e._showElement("klarnaPaymentsContainer"),e._hideElement("klarnaUnavailableError"),e._enableSubmitButton()):(e._hideElement("klarnaPaymentsContainer"),e._showElement("klarnaUnavailableError")),o.a.remove(t)}))}catch(t){e._hideElement("klarnaPaymentsContainer"),e._showElement("klarnaUnavailableError")}}this._handlePaymentMethodModal(),this._registerEvents()}},{key:"_registerEvents",value:function(){var e=this;document.getElementById("confirmOrderForm").addEventListener("submit",e._handleOrderSubmit.bind(this));var t=document.querySelectorAll("[name='paymentMethodId']");Array.prototype.forEach.call(t,(function(t){t.addEventListener("change",e._handlePaymentMethodChange.bind(e))}))}},{key:"_handlePaymentMethodChange",value:function(e){this._hideElements("klarnaPaymentsContainerModal"),this.getKlarnaCodeFromPaymentMethod(e.target.value)&&this._showElement("klarnaPaymentsContainerModal".concat(e.target.value))}},{key:"_handlePaymentMethodModal",value:function(){var e=this,t=document.querySelectorAll(".klarna-payment-method");Array.prototype.forEach.call(t,(function(t){var n=t.getAttribute("id"),a=e.getKlarnaCodeFromPaymentMethod(n);try{Klarna.Payments.load({container:"#klarnaPaymentsContainerModal".concat(n),payment_method_category:a,instance_id:n},(function(t){t.show_form||e._hideElement(n)}))}catch(t){e._hideElement(n)}}))}},{key:"_hideElements",value:function(e){var t=document.getElementsByClassName(e);Array.prototype.forEach.call(t,(function(e){e.hidden=!0}))}},{key:"_hideElement",value:function(e){var t=document.getElementById(e);t&&(t.hidden=!0)}},{key:"_showElement",value:function(e){var t=document.getElementById(e);t&&(t.hidden=!1)}},{key:"_emptyElement",value:function(e){var t=document.getElementById(e);t&&(t.innerHTML="")}},{key:"getKlarnaCodeFromPaymentMethod",value:function(e){var t=document.getElementById(e);return t?t.getAttribute("data-klarna-code"):""}},{key:"_disableSubmitButton",value:function(){var e=document.getElementById("confirmFormSubmit");e&&e.setAttribute("disabled","disabled")}},{key:"_enableSubmitButton",value:function(){var e=document.getElementById("confirmFormSubmit");e&&e.removeAttribute("disabled")}},{key:"_moveKlarnaModalContainer",value:function(e){var t=document.getElementById("klarnaModalContainer");e.parentElement.appendChild(t)}},{key:"_handleOrderSubmit",value:function(e){this.paymentCategory&&(this.authorization||(e.preventDefault(),r.a.create(),this._createAuthorization()))}},{key:"_createAuthorization",value:function(){var e=this;try{Klarna.Payments.authorize({auto_finalize:!0,payment_method_category:this.paymentCategory},e.customerData,(function(t){t.show_form||(e._hideElement("klarnaPaymentsContainer"),e._showElement("klarnaUnavailableError")),t.approved?(e._saveAuthorization(t),e._submitConfirmForm()):r.a.remove()}))}catch(t){e._hideElement("klarnaPaymentsContainer"),e._showElement("klarnaUnavailableError")}}},{key:"_saveAuthorization",value:function(e){this.authorization=e.authorization_token,this._addAuthorizationToForm(this.authorization)}},{key:"_addAuthorizationToForm",value:function(e){var t=document.getElementById("klarnaAuthorizationToken");t&&(t.value=e)}},{key:"_submitConfirmForm",value:function(){var e=document.getElementById("confirmOrderForm");e&&e.submit()}}])&&u(n.prototype,a),l&&u(n,l),t}(a.a);window.PluginManager.register("KlarnaPayments",s,"[data-is-klarna-payments]")}},[["Refz","runtime","vendor-node","vendor-shared"]]]);