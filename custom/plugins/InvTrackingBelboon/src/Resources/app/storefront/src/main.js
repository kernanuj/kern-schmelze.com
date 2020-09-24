import { COOKIE_CONFIGURATION_UPDATE } from 'src/plugin/cookie/cookie-configuration.plugin';

function eventCallback(updatedCookies) {
    if (typeof updatedCookies.belboon-enabled !== 'undefined') {
        // The cookie with the cookie attribute "myCookie" either is set active or from active to inactive
    } else {
        // The cookie with the cookie attribute "myCookie" was not updated
    }
}

document.$emitter.subscribe(COOKIE_CONFIGURATION_UPDATE, eventCallback);

