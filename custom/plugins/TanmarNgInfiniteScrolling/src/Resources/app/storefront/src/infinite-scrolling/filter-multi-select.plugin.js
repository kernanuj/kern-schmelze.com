import FilterMultiSelectPlugin from 'src/plugin/listing/filter-multi-select.plugin';

export default class TanmarInfiniteScrollingFilterMultiSelectPlugin extends FilterMultiSelectPlugin {

    /**
     * @private
     */
    _onChangeFilter() {
        var me = this, l = me.listing;
        
        if (l._tmisActive) {
            l._tmisListingOption = 'override';
            l._tmisVisitedPages = [];
            l._tmisNewPageRequestCounter = 0;
            l._tmisIsLoading = true;
            l._tmisLog('reset _onChangeFilter');
            l.changeListing();
        } else {
            super._onChangeFilter();
        }
    }

}
