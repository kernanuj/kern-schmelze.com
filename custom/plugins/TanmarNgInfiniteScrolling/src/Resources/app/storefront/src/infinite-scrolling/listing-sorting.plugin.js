
import ListingSortingPlugin from 'src/plugin/listing/listing-sorting.plugin';

export default class TanmarInfiniteScrollingListingSortingPlugin extends ListingSortingPlugin {

    onChangeSorting(event) {
        var me = this, l = me.listing;
        
        if (l._tmisActive) {
            me.options.sorting = event.target.value;
            l._tmisVisitedPages = [];
            l._tmisNewPageRequestCounter = 0;
            l._tmisIsLoading = true;
            l._tmisLog('reset onChangeSorting');
            l.changeListing();
        }else{
            super.onChangeSorting(event);
        }
    }

    afterContentChange() {
        // dont remove filter
        // this.listing.deregisterFilter(this);
    }
}