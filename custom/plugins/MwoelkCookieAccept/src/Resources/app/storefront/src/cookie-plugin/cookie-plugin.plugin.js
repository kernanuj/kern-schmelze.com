import Plugin from 'src/plugin-system/plugin.class';
import CookieStorage from 'src/helper/storage/cookie-storage.helper';
import DeviceDetection from 'src/helper/device-detection.helper';
import HttpClient from 'src/service/http-client.service';

let xhr = null;

export default class CookiePlugin extends Plugin {
    static options = {
        submitEvent: DeviceDetection.isTouchDevice() ? 'touchstart' : 'click',

        /**
         * True if the page should be relaoded after the user has accepted all cookies
         * @type boolean
         */
        reloadPage: false
    };

    init() {
        this._registerEvents(this.el);
    }

    _registerEvents() {
        const { submitEvent } = this.options;

        this.el.addEventListener(
            submitEvent,
            this.acceptAllCookies.bind(this)
        );
    }

    _getCookies(callback) {
        const url = window.router['frontend.cookie.offcanvas'];
        const client = new HttpClient(window.accessKey, window.contextToken);

        const cookieConfigurationPlugin = this._getCookieConfigurationPlugin();
        const { cookieSelector } = cookieConfigurationPlugin.options;

        // interrupt already running ajax calls
        if (xhr) xhr.abort();

        const cb = data => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(data, 'text/html');

            const cookies = Array.from(
                doc.querySelectorAll(cookieSelector)
            ).map(filteredInput => {
                const {
                    cookie,
                    cookieValue,
                    cookieExpiration,
                    cookieRequired
                } = filteredInput.dataset;
                return {
                    cookie,
                    value: cookieValue,
                    expiration: cookieExpiration,
                    required: cookieRequired
                };
            });

            callback(cookies);
        };

        xhr = client.get(url, cb);
    }

    _getCookieConfigurationPlugin() {
        const cookieConfigurationPlugins = window.PluginManager.getPluginInstances(
            'CookieConfiguration'
        );

        if (!cookieConfigurationPlugins) {
            console.error('CookieConfiguration is missing!');
            return null;
        }

        return cookieConfigurationPlugins[0];
    }

    acceptAllCookies() {
        const cookieConfigurationPlugin = this._getCookieConfigurationPlugin();
        const { cookiePreference } = cookieConfigurationPlugin.options;

        this._getCookies(cookies => {
            const cookieNames = [];

            cookies.forEach(({ cookie, value, expiration }) => {
                cookieNames.push(cookie);

                if (cookie && value) {
                    CookieStorage.setItem(cookie, value, expiration);
                }
            });

            CookieStorage.setItem(cookiePreference, '1', '30');

            cookieConfigurationPlugin._handleUpdateListener(cookieNames, []);
            cookieConfigurationPlugin._hideCookieBar();

            if(this.options.reloadPage) {
                window.location.reload();
            }
        });
    }
}
