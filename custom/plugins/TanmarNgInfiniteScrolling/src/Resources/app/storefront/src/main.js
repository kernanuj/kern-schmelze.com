// Import all necessary Storefront plugins and scss files

import TanmarInfiniteScrollingFilterBooleanPlugin from './infinite-scrolling/filter-boolean.plugin';
import TanmarInfiniteScrollingFilterMultiSelectPlugin from './infinite-scrolling/filter-multi-select.plugin';
import TanmarInfiniteScrollingFilterRangePlugin from './infinite-scrolling/filter-range.plugin';
import TanmarInfiniteScrollingFilterRatingPlugin from './infinite-scrolling/filter-rating.plugin';
import TanmarInfiniteScrollingFilterPropertySelectPlugin from './infinite-scrolling/filter-property-select.plugin';
import TanmarInfiniteScrollingListingSortingPlugin from './infinite-scrolling/listing-sorting.plugin';

import TanmarInfiniteScrolling from './infinite-scrolling/infinite-scrolling.plugin';
import polyfill from './infinite-scrolling/polyfill-intersection-observer';


if(document.querySelector('.is-tanmar-infinite-scrolling')){
    
    // init intersection observer polyfill
    polyfill(window, document);

    // Register them via the existing PluginManager
    const PluginManager = window.PluginManager;
    
    PluginManager.override('FilterBoolean', TanmarInfiniteScrollingFilterBooleanPlugin, '[data-filter-boolean]');
    PluginManager.override('FilterMultiSelect', TanmarInfiniteScrollingFilterMultiSelectPlugin, '[data-filter-multi-select]');
    PluginManager.override('FilterPropertySelect', TanmarInfiniteScrollingFilterPropertySelectPlugin, '[data-filter-property-select]');

    PluginManager.override('FilterRange', TanmarInfiniteScrollingFilterRangePlugin, '[data-filter-range]');
    PluginManager.override('FilterRating', TanmarInfiniteScrollingFilterRatingPlugin, '[data-filter-rating]');
    PluginManager.override('ListingSorting', TanmarInfiniteScrollingListingSortingPlugin, '[data-listing-sorting]');

    PluginManager.override('Listing', TanmarInfiniteScrolling, '[data-listing]');
}

if (module.hot) {
    module.hot.accept();
}

// .