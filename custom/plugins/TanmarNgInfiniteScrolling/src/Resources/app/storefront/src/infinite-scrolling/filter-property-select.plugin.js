import FilterPropertySelectPlugin from 'src/plugin/listing/filter-property-select.plugin'

export default class TanmarInfiniteScrollingFilterPropertySelectPlugin extends FilterPropertySelectPlugin {

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
            l._tmisLog('reset property _onChangeFilter');
            l.changeListing();
        } else {
            super._onChangeFilter();
        }
    }
}
