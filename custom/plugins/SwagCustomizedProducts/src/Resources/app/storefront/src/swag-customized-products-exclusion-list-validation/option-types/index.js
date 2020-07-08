/* global require */

/**
 * Provides an option type handler manager which allows an easy-to-use way to handle different input type
 * fields using an unified interface.
 *
 * @param {NativeEventEmitter} eventEmitter
 * @returns {{
 *  add: add,
 *  call: (function((Object|null), String, String=, ...[String|Object|Array]): *),
 *  handlers: (*|{}),
 *  get: get,
 *  name: string,
 *  clear: (function(Boolean=): boolean),
 *  has: (function(String): boolean),
 *  remove: remove}}
 */
export default (eventEmitter) => {
    /** @type {Object} Registry for the different type handler */
    let typeHandler = {};

    /**
     * Automatically loads handler files using the filesystem using webpack' require.context,
     * see {@link https://webpack.js.org/guides/dependency-management/#requirecontext}.
     *
     * The method iterates over the files found in the current folder and creates an array
     * where the key is the name of the handler and the value is the exclusion handler method itself.
     *
     * @returns {Object}
     */
    const autoloadHandlers = () => {
        const context = require.context('./', false, /(?<!index)\.js$/);
        return context.keys().reduce((accumulator, item) => {
            const module = context(item).default;
            module.type.forEach((type) => {
                accumulator[type] = module;
            });

            return accumulator;
        }, {});
    };

    /**
     * Clears the type handler registry. When `autoload` is truthy, the handler from the file system
     * will be loaded again, see {@link autoloadHandlers}
     *
     * @param {Boolean} [autoload=false]
     * @returns {Boolean}
     */
    const clear = (autoload = false) => {
        typeHandler = {};

        if (autoload) {
            typeHandler = autoloadHandlers();
        }

        return true;
    };

    /**
     * Checks if the registry has a handler associated to the provided type.
     *
     * @param {String} type
     * @returns {Boolean}
     */
    const has = (type) => {
        return Object.keys(typeHandler).includes(type);
    };

    /**
     * Adds an additional handler. Existing handler will not overridden per default. Existing handlers
     * can be overridden when `force` is truthy.
     *
     * @param {String} type
     * @param {Object} handler
     * @param {Boolean} [force=false]
     * @returns {Boolean}
     */
    const add = (type, handler, force = false) => {
        if (!force && (has(type))) {
            return false;
        }

        typeHandler[type] = handler;
        return true;
    };

    /**
     * Removes an existing handler from the registry.
     *
     * @param {String} type
     * @returns {Boolean}
     */
    const remove = (type) => {
        if (!has(type)) {
            return false;
        }

        delete typeHandler[type];
        return true;
    };

    /**
     * Gets a handler from the registry.
     *
     * @param {String} type
     * @returns {null|Object}
     */
    const get = (type) => {
        if (!has(type)) {
            return null;
        }

        return typeHandler[type];
    };

    /**
     * Calls a handler using the provided scope of provided type with the provided parameters.
     *
     * @param {Object|null} scope
     * @param {String} type
     * @param {String} [handlerType='validate']
     * @param {...String|Object|Array} params
     * @returns {*}
     */
    const call = (scope, type, handlerType = 'validate', ...params) => {
        const returnValue = typeHandler[type][handlerType].apply(
            scope,
            params
        );

        eventEmitter.publish(`swagCustomizedProducts/optionType/${handlerType}/${type}`, {
            params
        });

        return returnValue;
    };

    // Initially load type handlers
    typeHandler = autoloadHandlers();

    return {
        name: 'option-type-handler-manager',
        handlers: typeHandler,
        clear,
        has,
        get,
        add,
        remove,
        call
    };
};
