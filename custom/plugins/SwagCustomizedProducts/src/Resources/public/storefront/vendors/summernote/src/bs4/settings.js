import $ from 'jquery';
import ui from './ui';
import '../base/settings.js';

$.summernote = $.extend($.summernote, {
    ui_template: ui,
    interface: 'bs4',
});

$.summernote.options.styleTags = [
    'p',
    { title: 'Blockquote', tag: 'blockquote', className: 'blockquote', value: 'blockquote' },
    'pre', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
];
