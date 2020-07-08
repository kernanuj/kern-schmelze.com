/* eslint-disable import/no-unresolved */

import Plugin from 'src/plugin-system/plugin.class';
import '../../../../public/storefront/vendors/summernote/src/summernote';
import '../../../../public/storefront/vendors/summernote/src/bs4/settings';
import '../../../../public/storefront/vendors/summernote/lang/summernote-de-DE';

/**
 * HTML editor component that is loaded when the 'data-swag-customized-products-html-editor' attribute exists.
 */
export default class SwagCustomizedProductsHtmlEditor extends Plugin {
    static options = {
        /**
         * Locale of the editor
         * Available locales (only those are loaded):
         * - en-GB
         * - de-DE
         *
         * Fallback locale:
         * - en-GB
         */
        locale: 'en-GB',

        /**
         * The placeholder to show when the editor is empty.
         */
        placeholder: '',

        /**
         * The option object for the html editor (https://summernote.org/)
         */
        editorOptions: {

            width: '100%',

            height: 300,

            tabsize: 2,

            /**
             * Focus immediately
             */
            focus: false,

            /**
             * Button configuration for the toolbar.
             * The toolbar is divided into groups of actions (buttons) that can be specified like so:
             * - ['groupName', ['action1', 'action2', 'action3']]
             * The groupName is only an identifier for the group and actions are displayed as buttons
             * which are connected to the other buttons inside the group. Between each group is some space.
             *
             * Available actions are:
             * - style
             * - bold
             * - italic
             * - underline
             * - strikethrough
             * - clear
             * - fontname
             * - fontsize
             * - color
             * - ul
             * - ol
             * - paragraph
             * - table
             * - link
             * - picture
             * - video
             * - fullscreen
             * - codeview (dangerous - allows html editing)
             * - help (shows shortcuts)
             */
            toolbar: [
                ['font', ['bold', 'italic', 'underline', 'strikethrough']]
            ],

            /**
             * Possible styling options if accessible via action or keymap
             * Available:
             * - p
             * - blockquote
             * - pre
             * - h1
             * - h2
             * - h3
             * - h4
             * - h5
             * - h6
             */
            styleTags: ['p', 'blockquote', 'pre', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6'],

            /**
             * Key shortcuts for different actions that will also be displayed in the help action
             * (they are also accessible when the action itself is not defined in the toolbar!)
             *
             * Available actions (with default keys on pc / mac):
             * - escape (ESC / ESC)
             * - insertParagraph (ENTER / ENTER)
             * - undo (CTRL+Z / CMD+Z)
             * - redo (CTRL+Y / CMD+SHIFT+Z)
             * - tab (TAB / TAB)
             * - untab (SHIFT+TAB / SHIFT+TAB)
             * - bold (CTRL+B / CMD+B)
             * - italic (CTRL+I / CMD+I)
             * - underline (CTRL+U / CMD+U)
             * - strikethrough (CTRL+SHIFT+S / CMD+SHIFT+S)
             * - removeFormat (CTRL+BACKSLASH / CMD+BACKSLASH)
             * - justifyLeft (CTRL+SHIFT+L / CMD+SHIFT+L)
             * - justifyCenter (CTRL+SHIFT+E / CMD+SHIFT+E)
             * - justifyRight (CTRL+SHIFT+R / CMD+SHIFT+R)
             * - justifyFull (CTRL+SHIFT+J / CMD+SHIFT+J)
             * - insertUnorderedList (CTRL+SHIFT+NUM7 / CMD+SHIFT+NUM7)
             * - insertOrderedList (CTRL+SHIFT+NUM8 / CMD+SHIFT+NUM8)
             * - outdent (CTRL+LEFTBRACKET / CMD+LEFTBRACKET)
             * - indent (CTRL+RIGHTBRACKET / CMD+RIGHTBRACKET)
             * - formatPara (CTRL+NUM0 / CMD+NUM0)
             * - formatH1 (CTRL+NUM1 / CMD+NUM1)
             * - formatH2 (CTRL+NUM2 / CMD+NUM2)
             * - formatH3 (CTRL+NUM3 / CMD+NUM3)
             * - formatH4 (CTRL+NUM4 / CMD+NUM4)
             * - formatH5 (CTRL+NUM5 / CMD+NUM5)
             * - formatH6 (CTRL+NUM6 / CMD+NUM6)
             * - insertHorizontalRule (CTRL+ENTER / CMD+ENTER)
             * - linkDialog.show (CTRL+K / CMD+K) - dangerous, it allows link insertion (even if no action in toolbar exists)
             *
             */
            keyMap: {
                pc: {
                    ESC: 'escape',
                    ENTER: 'insertParagraph',
                    'CTRL+Z': 'undo',
                    'CTRL+Y': 'redo',
                    TAB: 'tab',
                    'SHIFT+TAB': 'untab',
                    'CTRL+B': 'bold',
                    'CTRL+I': 'italic',
                    'CTRL+U': 'underline',
                    'CTRL+SHIFT+S': 'strikethrough',
                    'CTRL+BACKSLASH': 'removeFormat'
                },

                mac: {
                    ESC: 'escape',
                    ENTER: 'insertParagraph',
                    'CMD+Z': 'undo',
                    'CMD+SHIFT+Z': 'redo',
                    TAB: 'tab',
                    'SHIFT+TAB': 'untab',
                    'CMD+B': 'bold',
                    'CMD+I': 'italic',
                    'CMD+U': 'underline',
                    'CMD+SHIFT+S': 'strikethrough',
                    'CMD+BACKSLASH': 'removeFormat'
                }
            },

            /**
             * Callbacks for some events. We disable image uploads here too, because it is possible to drag and drop,
             * even if there is no action in the toolbar / no keymap for this.
             */
            callbacks: {
                onImageUpload: (data) => {
                    delete data[0]; // prevent image uploads per drag and drop
                }
            },

            /**
             * Disable the resize toolbar at the bottom of the editor
             */
            disableResizeEditor: true
        }
    };

    /**
     * Initialization of the html editor that creates the summernote instance and configures it.
     */
    init() {
        this.parentEl = this.el.closest('form');

        if (this.options.editorOptions.lang === undefined) {
            this.options.editorOptions.lang = this.options.locale;
        }

        if (this.options.editorOptions.placeholder === undefined) {
            this.options.editorOptions.placeholder = this.options.placeholder;
        }

        this.options.editorOptions.callbacks = {
            // Callback which fires an input & change event on the underlying textarea for validation and exclusions
            onChange: () => {
                ['input', 'change'].forEach((eventName) => {
                    const event = document.createEvent('Event');

                    event.initEvent(eventName, true, false);
                    this.el.dispatchEvent(event);
                });
            }
        };

        /* eslint-env jquery */
        this.$htmlEditor = $(this.el).summernote(this.options.editorOptions);

        this._workaroundToMakeOriginalFocusable();
        this._workaroundToFocusSummernoteInsteadOriginal();
    }

    /**
     * Fix to make the original element focusable.
     * This allows focus as a form field (that is important for validation).
     *
     * @private
     */
    _workaroundToMakeOriginalFocusable() {
        // change order: insert original input after the summernote instance
        const summernoteElement = $(this.el).next();
        $(this.el).detach().insertAfter(summernoteElement);

        // remove custom style on original input element that was added by summernote (to allow focus on that element)
        $(this.el).removeAttr('style');
        $(this.el).css({
            opacity: '0',
            width: '0',
            height: '0',
            margin: '0',
            padding: '0'
        });
    }

    /**
     * Apply focus correctly to the summernote html editor instead of the hidden original form field.
     *
     * @private
     */
    _workaroundToFocusSummernoteInsteadOriginal() {
        // fix for not scrolling to original form element
        this.el._ignoreValidityEvent = true;

        // pass focus events to summernote
        $(this.el).focus(() => {
            this.el._ignoreValidityEvent = true;
            $(this.el).summernote('focus');
        });
    }
}
