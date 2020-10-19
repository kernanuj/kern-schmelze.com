import ListingPlugin from 'src/plugin/listing/listing.plugin';
import ElementReplaceHelper from 'src/helper/element-replace.helper';
import PluginManager from 'src/plugin-system/plugin.manager';
import LoadingIndicatorUtil from 'src/utility/loading-indicator/loading-indicator.util';

/**
 * @author Tanmar Webentwicklung <info@tanmar.de>
 * @version 1.0.8
 */
export default class TanmarInfiniteScrolling extends ListingPlugin {
    
    init() {
        var me = this;
        
        //
        me._tmisVersion = '1.0.8';
        
        // debug for logs
        me._tmisDebug = false;
        
        // plugin is active
        me._tmisActive = false;
        
        // page counter, only load one automatically, show box after that
        me._tmisNewPageRequestCounter = 0;

        // loading indicator
        me._tmisIsLoading = false;

        // page history
        me._tmisVisitedPages = [];

        // get listing html, append prev/next box
        me._tmisListingElement = null;

        // type how to add listing html
        me._tmisListingOption = '';
        
        // snippet holder
        me._tmisSnippets = {};
        
        me._tmisNewPageRequestMax = 1;
        
        me._listingRowSelector = '.cms-element-product-listing-wrapper .cms-listing-row';
        
        me._paginationSelector = '.cms-element-product-listing-wrapper .pagination-nav';
        
        // super init
        super.init();
        
        me._tmisInit();
    }
    
    _tmisInit(){
        var me = this;
        
        if(document.querySelector('body.is-tanmar-infinite-scrolling')){

            // hello world
            me._tmisLog('welcome to TanmarInfiniteScrolling v' + me._tmisVersion);
            
            // load snippets
            if(window._tanmarInfiniteScrolling){
                me._tmisLog('config ', window._tanmarInfiniteScrolling);
                
                me._tmisNewPageRequestMax = parseInt(window._tanmarInfiniteScrolling.pages, 10);
                me._tmisSnippets = window._tanmarInfiniteScrolling.snippets;
                me._tmisLog('snippets success ', me._tmisSnippets);
                
            }else{
                me._tmisLog('TanmarInfiniteScrolling data error');
                return;
            }
            
            me._tmisActive = true;

            // hide navigation
            var paginations = document.querySelectorAll('.pagination-nav');
            if(paginations.length <= 0){
                me._tmisLog('error ".pagination-nav" not found');
                return;
            }
            paginations.forEach(nav => {
                nav.style.display = 'none';
            });

            // register intersection observer
            me._tmisRegisterIntersectionObserver();
            me._tmisObserveLastProductBox();

            // 
            me.currentPage = 1;
            me.lastPage = 1;
            
            if(paginations && paginations[0]){
                const currentPageInput = paginations[0].querySelector('.page-item.active input');
                if(currentPageInput){
                    me.currentPage = parseInt(currentPageInput.value, 10);
                }else{
                    me._tmisLog('can\'t find \'page-item.active input\'');
                }
                
                const lastPageInput = paginations[0].querySelector('.page-item.page-last input');
                if(lastPageInput){
                    me.lastPage = parseInt(lastPageInput.value, 10);
                }else{
                    me._tmisLog('can\'t find \'page-item.page-last input\'');
                }
            }

            // add current page to history
            me._tmisVisitedPages.push(me.currentPage);
            
            // get listing html
            me._tmisListingElement = document.querySelector(this.options.cmsProductListingSelector);

            me._tmisLog('  currentPage: ' + me.currentPage + ' - lastPage: ' + me.lastPage);

            // check for button on top
            if(me.currentPage > 1){
                me._tmisBuildPrevInfoBox();
            }

            // immediately show info box on max 0
            if(me._tmisNewPageRequestMax == 0){
                me._tmisBuildNextInfoBox();
            }
        }
    }
    
    /**
     * 
     */
    _tmisLog(){
        var me = this;
        if(me._tmisDebug){
            console.log(...arguments);
        }
    }
    
    /**
     * 
     */
    _tmisRegisterIntersectionObserver(){
        var options = {
            root: null, 
            rootMargin: '0px',
            threshold: 0.5,
        };
        this.iObserver = new IntersectionObserver(this._tmisOnIntersection.bind(this), options);
    }
    
    /**
     * 
     */
    _tmisObserveLastProductBox(){
        var me = this;
        
        var all = document.querySelectorAll('.card.product-box');
        if(all.length <= 0){
            me._tmisLog('error "last product" not found');
            return;
        }
        me._tmisLog('observe element');
        me._tmisLog(all[all.length-1]);
        
        me.iObserver.observe(all[all.length-1]);
    }
    
    /**
     * 
     */
    _tmisOnIntersection(entries, observer){
        var me = this;
        
        // on each intersection obj
        entries.forEach(entry => {

            // on "in view"
            if (entry.intersectionRatio > 0 && !me._tmisIsLoading) {

                // check navigation html
                const next = document.querySelector('.pagination .page-next');
                if(next){
                    
                    me._tmisLog('on intersection');
                    
                    if(!next.classList.contains('disabled')){
                        if(me._tmisNewPageRequestCounter < me._tmisNewPageRequestMax){
                            
                            me._tmisRequestNewPage(next, 'append');
                            
                            me._tmisLog('request new page, unobserve element');
                            me._tmisLog(entry.target);
                            
                            observer.unobserve(entry.target);
                        }
                        
                    }else{
                        me._tmisLog('no new page, unobserve element');
                        me._tmisLog(entry.target);
                        
                        // no next page, remove observer
                        observer.unobserve(entry.target);
                    }
                }

            }
            
        });

    }
    
    /**
     * 
     */
    _tmisRequestNewPage(nextOrPrev, listingOption){
        var me = this;
        
        me._tmisIsLoading = true;
        
        // set option
        switch (listingOption) {
            case 'append':
                me._tmisListingOption = 'append';
                break;
            case 'prepend':
                me._tmisListingOption = 'prepend';
                break;
            default:
            case 'override':
                me._tmisListingOption = 'override';
                break;
        }
        

        // get next/prev page or fix page nr
        var nextPage;
        if(isNaN(nextOrPrev)){
            nextPage = parseInt(nextOrPrev.querySelector('input').value, 10);
        }else{
            nextPage = parseInt(nextOrPrev, 10);
        }
        
        // load listing pagination plugin
        const ListingPaginationPlugin = PluginManager.getPluginInstanceFromElement(document.querySelector('[data-listing-pagination]'),'ListingPagination');

        // request new page
        ListingPaginationPlugin.onChangePage({target:{value:nextPage}});

        // increase page request counter
        me._tmisNewPageRequestCounter++;

        // create and append loading
        me._tmisBuildLoading();
    }
    
    /**
     * 
     */
    _tmisBuildLoading(){
        var me = this;
        
        const div = document.createElement('div');
        div.classList.add('text-center');
        div.classList.add('infinite-scrolling-loading');
        
        const loader = new LoadingIndicatorUtil(div);
        
        var listing = document.querySelectorAll(me._listingRowSelector);
        const listingLast = listing[listing.length - 1];

        // check direction
        switch (me._tmisListingOption) {
            case 'append':
                listingLast.parentNode.insertBefore(div, listingLast.nextSibling);
                break;
            case 'prepend':
            default:
                listing[0].parentNode.insertBefore(div, listing[0]);
                break;
        }
        
        loader.create();
    }
    
    /**
     * 
     */
    _tmisBuildPrevInfoBox(){
        var me = this;

        const div = document.createElement('div');
        div.classList.add('text-center');
        div.classList.add('infinite-scrolling-button-prev');

        var prevPage = me.currentPage - 1;

        if(prevPage > 0){
            const width = prevPage/(me.lastPage>0?me.lastPage:1)*100;

            var naviSnippet = me._tmisSnippets.prev.navi.split('{x}').join(prevPage).split('{y}').join(me.lastPage);
            
            // box template
            div.innerHTML = `<button class="btn btn-block btn-buy">${me._tmisSnippets.prev.btn}</button>
                            <span class="tanmar-infinity-scrolling-button-text">
                                <span>${naviSnippet}</span>
                                <span class="tanmar-infinity-scrolling-button-bar">
                                    <span style="width: ${width}%"></span>
                                </span>
                            </span>`;

            me._tmisListingElement.insertBefore(div, me._tmisListingElement.firstChild);


            var btn = document.querySelector('.infinite-scrolling-button-prev button');
            if(btn){
                btn.addEventListener('click', function(){
                    var me = this[0];
                    var targetPage = this[1];

                    if(!me._tmisIsLoading){
                        me._tmisRequestNewPage(targetPage, 'prepend');

                        // remove box
                        const div = document.querySelector('.infinite-scrolling-button-prev');
                        div.parentNode.removeChild(div);
                    }
                }.bind([me,prevPage]));
            }
        }
    }
    
    /**
     * 
     */
    _tmisBuildNextInfoBox(){
        var me = this;

        var nextPage = parseInt(me.currentPage,10) + 1;
        
        me._tmisLog('nextPage = ' + nextPage + ' lastPage = ' + me.lastPage);
        
        
        // only build if next page wasnt loaded
        if(me._tmisVisitedPages.indexOf(nextPage) < 0 && nextPage <= me.lastPage){

            const div = document.createElement('div');
            div.classList.add('text-center');
            div.classList.add('infinite-scrolling-button-more');
            
            // current page, not next page
            const width = me.currentPage/(me.lastPage>0?me.lastPage:1)*100;

            var naviSnippet = me._tmisSnippets.next.navi.split('{x}').join(nextPage).split('{y}').join(me.lastPage);
            // box template
            div.innerHTML = `<button class="btn btn-block btn-buy">${me._tmisSnippets.next.btn}</button>
                            <span class="tanmar-infinity-scrolling-button-text">
                                <span>${naviSnippet}</span>
                                <span class="tanmar-infinity-scrolling-button-bar">
                                    <span style="width: ${width}%"></span>
                                </span>
                            </span>`;

            me._tmisListingElement.appendChild(div);


            var btn = document.querySelector('.infinite-scrolling-button-more button');
            if(btn){
                btn.addEventListener('click', function(){
                    var me = this[0];
                    var targetPage = this[1];
                    
                    if(!me._tmisIsLoading){
                        me._tmisRequestNewPage(targetPage, 'append');

                        // remove box
                        const div = document.querySelector('.infinite-scrolling-button-more');
                        div.parentNode.removeChild(div);
                    }
                }.bind([me,nextPage]));
            }
        }
    }
    
    /**
     * Inject the HTML of the filtered products to the page.
     *
     * @param {String} response - HTML of filtered product data.
     */
    renderResponse(response) {
        var me = this;
        
        if(me._tmisActive){

            // remove loading
            me._tmisIsLoading = false;
            document.querySelectorAll('.infinite-scrolling-loading').forEach(e => {
                e.parentNode.removeChild(e);
            });

            // parse response
            var responseHtml = (new DOMParser()).parseFromString(response, 'text/html');

            // get response html
            var content = responseHtml.querySelector(me._listingRowSelector);
            if(!content){
                me._tmisLog('content is null, responseHtml:');
                me._tmisLog(responseHtml);
            }

            // get the pagination html
            var pagination = responseHtml.querySelector(me._paginationSelector);
            if(pagination){
                // change shopware navi
                ElementReplaceHelper.replaceElement(pagination, document.querySelectorAll('.pagination-nav'));
            
                // get the current page
                me.currentPage = parseInt(pagination.querySelector('.page-item.active input').value,10);

                // get the last page, changed maybe by filter
                me.lastPage = parseInt(pagination.querySelector('.page-item.page-last input').value,10);
            }else{
                // no products
                me.currentPage = 1;
                me.lastPage = 1;
            }
            
            // check if page already loaded
            if(me._tmisVisitedPages.indexOf(me.currentPage) < 0){

                // add the current page to history
                me._tmisVisitedPages.push(me.currentPage);

                // 
                me._tmisLog('currentPage = ' + me.currentPage + ' - lastPage = ' + me.lastPage + ' - _tmisVisitedPages:');
                me._tmisLog(me._tmisVisitedPages);

                var listing = document.querySelectorAll(me._listingRowSelector);
                if(listing.length > 0){

                    // no let in switch
                    const listingLast = listing[listing.length - 1];
                    let div = null;

                    me._tmisLog('renderResponse "' + me._tmisListingOption + '"');

                    // check direction
                    switch (me._tmisListingOption) {
                        case 'append':

                            //listingLast.parentNode.insertBefore(content, listingLast.nextSibling);
                            listingLast.innerHTML += content.innerHTML;
                            
                            if(me._tmisNewPageRequestCounter >= me._tmisNewPageRequestMax){
                                // dont request new page, instead show info box
                                me._tmisBuildNextInfoBox();
                            }

                            break;
                        case 'prepend':

                            //listing[0].parentNode.insertBefore(content, listing[0]);
                            listingLast.innerHTML = content.innerHTML + listingLast.innerHTML;

                            if(me.currentPage > 1){
                                me._tmisBuildPrevInfoBox();
                            }

                            break;
                        default:
                        case 'override':
                            Array.from(listing).forEach((list,i) => {
                                if(i == 0){
                                    list.innerHTML = content.innerHTML;
                                }else{
                                    if(list && list.parentNode){
                                        list.parentNode.removeChild(list);
                                    }
                                }
                            });

                            div = document.querySelector('.infinite-scrolling-button-prev');
                            if(div){
                                div.parentNode.removeChild(div);
                            }
                            div = document.querySelector('.infinite-scrolling-button-more')
                            if(div){
                                div.parentNode.removeChild(div);
                            }

                            if(me.lastPage > me.currentPage){
                                me._tmisNewPageRequestCounter = 0;
                            }

                            if(me._tmisNewPageRequestCounter >= me._tmisNewPageRequestMax){
                                me._tmisBuildNextInfoBox();
                            }
                            
                            break;
                    }

                    // filter update
                    this._registry.forEach((item) => {
                        if (typeof item.afterContentChange === 'function') {
                            item.afterContentChange();
                        }
                    });

                    window.PluginManager.initializePlugins();
                    
                    // register new oberve
                    me._tmisObserveLastProductBox();
                    
                    // image fix
                    me._tmisAfterContentChange(content);
                }

                me._tmisListingOption = '';

                // check here for display block
            }else{
                me._tmisLog('page ' + me.currentPage + ' already loaded');
            }
        }else{
            super.renderResponse(response);
        }
    }
    
    /**
     * 
     */
    _tmisAfterContentChange(content){
        if(content){
            var a = content.querySelectorAll('img');
            var b = content.querySelectorAll('img');
            a.forEach((img,index) => {
                img.outerHTML = b[index].outerHTML;
            });
        }
    }
    
    /**
     * 
     */
    resetAllFilter() {
        var me = this;
        if(me._tmisActive){
            me._tmisListingOption = 'override';
            me._tmisVisitedPages = [];
            me._tmisNewPageRequestCounter = 0;
            me._tmisIsLoading = true;
            me._tmisLog('reset resetAllFilter');
        }
        super.resetAllFilter();
    }
    
    /**
     * 
     */
    resetFilter(label) {
        var me = this;
        if(me._tmisActive){
            me._tmisListingOption = 'override';
            me._tmisVisitedPages = [];
            me._tmisNewPageRequestCounter = 0;
            me._tmisIsLoading = true;
            me._tmisLog('reset resetFilter');
        }
        super.resetFilter(label);
    }
}