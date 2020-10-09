/**
* Update slider height for BaseSlider and GallerySlider plugins when lazy images have been loaded.
*/
import PluginManager from 'src/plugin-system/plugin.manager';

export default function () {
    let plugins = PluginManager.getPluginInstances('BaseSlider');
    plugins = plugins.concat( PluginManager.getPluginInstances('GallerySlider'));

    plugins.forEach(function (plugin) {
        let slider = plugin._slider,
            images = plugin.el.querySelectorAll('.maxia-lazy-image');

        if (typeof slider !== 'object')
            return;

        images.forEach(function (image) {
            image.addEventListener('lazyloaded', function () {
                slider.updateSliderHeight();
            });
        })
    });
}