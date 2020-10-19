import FilterRatingPlugin from 'src/plugin/listing/filter-rating.plugin';

export default class TanmarInfiniteScrollingFilterRatingPlugin extends FilterRatingPlugin {

    /**
     * @private
     */
    _onChangeRating() {
        var me = this, l = me.listing;
        
        if (l._tmisActive) {
            l._tmisListingOption = 'override';
            l._tmisVisitedPages = [];
            l._tmisNewPageRequestCounter = 0;
            l._tmisIsLoading = true;
            l._tmisLog('reset _onChangeRating');
            l.changeListing();
        } else {
            super._onChangeRating();
        }
    }

}
