import FilterBooleanPlugin from 'src/plugin/listing/filter-boolean.plugin';

export default class TanmarInfiniteScrollingFilterBooleanPlugin extends FilterBooleanPlugin {

    /**
     * @private
     */
    _onChangeCheckbox() {
        var me = this, l = me.listing;
        
        if(l._tmisActive){
            l._tmisListingOption = 'override';
            l._visitedPagesClear();
            l._tmisNewPageRequestCounter = 0;
            l._tmisIsLoading = true;
            l._tmisLog('  reset _onChangeCheckbox');
            l.changeListing();
        }else{
            super._onChangeCheckbox();
        }
    }
}
