(this.webpackJsonp=this.webpackJsonp||[]).push([["swag-cms-extensions"],{"0SmQ":function(e,n,s){var i=s("wEWP");"string"==typeof i&&(i=[[e.i,i,""]]),i.locals&&(e.exports=i.locals);(0,s("SZ7m").default)("116d6ee0",i,!0,{})},"5Rju":function(e,n,s){},"8b2m":function(e,n,s){var i=s("5Rju");"string"==typeof i&&(i=[[e.i,i,""]]),i.locals&&(e.exports=i.locals);(0,s("SZ7m").default)("593997d0",i,!0,{})},"8ezr":function(e){e.exports=JSON.parse('{"swag-cms-extensions":{"error-codes":{"SWAG_SCROLL_NAVIGATION_EMPTY_FIELD_WHEN_ACTIVE_ERROR":"This field must not be empty, if the navigation point is active.","SWAG_SCROLL_NAVIGATION_FIELD_TOO_LONG_ERROR":"The maximum size of this field has been exceeded.","SWAG_SCROLL_NAVIGATION_DUPLICATE_VALUE_ERROR":"This field\'s value is already in use."},"sw-cms":{"detail":{"quickview":{"showQuickviewLabel":"Use Quickview","showQuickviewHelpText":"When clicking on product-related blocks, the Quickview is displayed instead of the detail page.","sidebarHeaderBlockBehaviorSettings":"Quickview"},"scrollNavigation":{"title":"Scroll navigation","anchorSwitch":"Set navigation point","anchorTitle":"Navigation point name","anchorTitleHelpText":"The navigation points are displayed in the storefront as a navigatable list of available sections.","duplicateDisplayNameMessage":"This navigation point already exists: \\"{currentName}\\".","invalidDisplayNameLengthMessage":"At least one of your active navigation points has no name. | The name of this navigation point exceeds the maximum length of 255 characters: \\"{truncatedName}\\"","pageSettings":{"title":"Scroll navigation","description":"To use animated scrolling, make sure you have activated the necessary navigation points and assigned names.","advancedSettingsLabel":"Advanced settings","activeLabel":"Animated scrolling","activeHelpText":"Animated scrolling makes navigation between navigation points more fluid. Values between 500ms and 1000ms are recommended for a smooth scrolling experience.","durationLabel":"Duration","durationSuffix":"ms","easingLabel":"Scroll behaviour","easingDegreeLabel":"Degree of trajectory","easingDegreeHelpText":"The degree indicates the magnitude of the acceleration or deceleration. The lower the degree, the smoother the scrolling. A value between 2 and 5 is recommended for intuitive scrolling. This value does not affect bouncy or linear scrolling.","bouncyLabel":"Bouncy scrolling","easing":{"linear":"Constant (Linear)","in":"Accelerating (Ease In)","out":"Decelerating (Ease Out)","inOut":"Accelerating & Decelerating (Ease InOut)"}}}},"sidebar":{"emptyAnchorName":"(No name has been set)"},"section":{"actions":{"viewports":{"tooltipPrefix":"Navigation point:","emptyAnchorName":"(No name has been set)"},"pageForm":{"labelPrefix":"Navigation point","emptyAnchorName":"(No name has been set)"}}}}}}')},Ew5G:function(e,n,s){},IlhW:function(e,n){e.exports='{% block sw_cms_page_form_section_name_wrapper %}\n    {% parent %}\n\n    <div v-if="getScrollNavigationBySection(section) && getScrollNavigationBySection(section).active"\n         class="sw-cms-page-form__section-action sw-cms-page-form__section-anchor">\n\n        {% block sw_cms_page_form_section_anchor_icon %}\n            <sw-icon class="sw-cms-page-form__section-action-icon sw-cms-page-form__section-anchor-icon"\n                     name="default-action-tags"\n                     size="20">\n            </sw-icon>\n        {% endblock %}\n\n        {% block sw_cms_page_form__section_anchor %}\n            <span class="sw-cms-page-form__section-action-label sw-cms-page-form__section-anchor-label">\n                {{ getScrollNavigationLabel(section) }}\n            </span>\n        {% endblock %}\n    </div>\n{% endblock %}'},JC3F:function(e,n,s){"use strict";s.r(n);var i=s("UFuc"),t=s("8ezr");Shopware.Locale.extend("de-DE",i),Shopware.Locale.extend("en-GB",t);var a=s("QIAG"),o=s.n(a);s("LW0H");const{Component:c,Application:{view:{setReactive:l}}}=Shopware;c.register("swag-cms-extensions-block-behavior-config",{template:o.a,inject:["repositoryFactory"],model:{prop:"block",event:"block-update"},props:{block:{type:Object,required:!0,default:()=>({})}},computed:{productRelatedBlocks:()=>["product-listing","product-slider","product-three-column"],productRelatedSlots:()=>["product-box","product-slider"],blockIsProductRelated(){return this.block.slots.reduce((e,n)=>e||(e=this.productRelatedSlots.includes(n.type)),!1)||this.productRelatedBlocks.includes(this.block.type)},quickviewRepository(){return this.repositoryFactory.create("swag_cms_extensions_quickview")},quickviewExtensionDefined(){return void 0!==this.block.extensions.swagCmsExtensionsQuickview},quickview(){return this.quickviewExtensionDefined?this.block.extensions.swagCmsExtensionsQuickview:this.blockIsProductRelated?this.quickviewRepository.create(Shopware.Context.api):{}}},methods:{quickviewActiveChanged(e){this.quickviewExtensionDefined||(l(this.block.extensions,"swagCmsExtensionsQuickview",this.quickview),l(this.block.extensions.swagCmsExtensionsQuickview,"active",e))}}});var r=s("jpIc"),g=s.n(r);s("0SmQ");const{Component:d,Application:{view:{setReactive:m}}}=Shopware;d.register("swag-cms-extensions-page-scroll-behavior-config",{template:g.a,inject:["repositoryFactory"],model:{prop:"page",event:"page-change"},props:{page:{type:Object,required:!0}},data:()=>({defaults:{active:!1,duration:1e3,easing:"inOut",easingDegree:3,bouncy:!1},easings:["linear","in","out","inOut"]}),computed:{scrollNavigationPageSettingsRepository(){return this.repositoryFactory.create("swag_cms_extensions_scroll_navigation_page_settings")},scrollNavigationPageSettingsDefined(){return void 0!==this.page.extensions.swagCmsExtensionsScrollNavigationPageSettings},scrollNavigationPageSettings(){return this.scrollNavigationPageSettingsDefined?this.page.extensions.swagCmsExtensionsScrollNavigationPageSettings:this.scrollNavigationPageSettingsRepository.create(Shopware.Context.api)},pageHasNavigationPoints(){return this.page.sections.some(e=>{const n=e.extensions.swagCmsExtensionsScrollNavigation;return n&&n.active})},smoothScrollingActive(){return this.pageHasNavigationPoints&&this.scrollNavigationPageSettings.active},easingTypes(){return this.easings.map(e=>({value:e,label:this.$tc("swag-cms-extensions.sw-cms.detail.scrollNavigation.pageSettings.easing."+e)}))},easingDegreeDisabled(){return!this.smoothScrollingActive||"linear"===this.scrollNavigationPageSettings.easing||!0===this.scrollNavigationPageSettings.bouncy}},created(){this.createdComponent()},methods:{createdComponent(){this.setDefaults()},setDefaults(){Object.entries(this.defaults).forEach(([e,n])=>{void 0===this.scrollNavigationPageSettings[e]&&(this.scrollNavigationPageSettings[e]=n,this.pageSettingsActiveChanged())})},pageSettingsActiveChanged(){m(this.page.extensions,"swagCmsExtensionsScrollNavigationPageSettings",this.scrollNavigationPageSettings),this.$emit("page-change",this.page)}}});var _=s("qeqr"),v=s.n(_);s("vFYS");const{Component:p,Application:{view:{setReactive:h}}}=Shopware,{mapPropertyErrors:w}=p.getComponentHelper();p.register("swag-cms-extensions-section-scroll-behavior-config",{template:v.a,inject:["repositoryFactory"],model:{prop:"section",event:"section-change"},props:{section:{type:Object,required:!0}},computed:{scrollNavigationRepository(){return this.repositoryFactory.create("swag_cms_extensions_scroll_navigation")},scrollNavigationExtensionDefined(){return void 0!==this.section.extensions.swagCmsExtensionsScrollNavigation},swagCmsExtensionsScrollNavigation(){return this.scrollNavigationExtensionDefined?this.section.extensions.swagCmsExtensionsScrollNavigation:this.scrollNavigationRepository.create(Shopware.Context.api)},...w("swagCmsExtensionsScrollNavigation",["displayName"])},methods:{scrollNavigationActiveChanged(){h(this.section.extensions,"swagCmsExtensionsScrollNavigation",this.swagCmsExtensionsScrollNavigation),this.$emit("section-change",this.section)}}});var b=s("gHnw"),u=s.n(b);s("8b2m");const{Component:N,State:S}=Shopware;N.override("sw-cms-sidebar",{template:u.a,data:()=>({sectionHeaderWrapperClass:"sw-cms-sidebar__navigator-section-header-wrapper"}),computed:{sectionHeaderWrapperClasses(){return[this.sectionHeaderWrapperClass]}},watch:{"cmsPageState.currentPage"(){S.commit("cmsPageState/removeSelectedSection")}},methods:{getScrollNavigationBySection:e=>e.extensions.swagCmsExtensionsScrollNavigation,scrollNavigationIsActive(e){return this.getScrollNavigationBySection(e)&&this.getScrollNavigationBySection(e).active},navigatorSectionHeaderWrapperClass(e){const n=this.getScrollNavigationBySection(e);return n&&n.active?[this.sectionHeaderWrapperClasses]:""},scrollNavigationAnchorTooltip(e){const n=this.getScrollNavigationBySection(e);return{message:n&&null!==n.displayName?n.displayName:this.$tc("swag-cms-extensions.sw-cms.sidebar.emptyAnchorName")}}}});var x=s("IlhW"),f=s.n(x);s("beRM");const{Component:k}=Shopware;k.override("sw-cms-page-form",{template:f.a,methods:{getScrollNavigationBySection:e=>e.extensions.swagCmsExtensionsScrollNavigation,getScrollNavigationLabel(e){const n=this.$tc("swag-cms-extensions.sw-cms.section.actions.pageForm.labelPrefix"),s=this.getScrollNavigationBySection(e);return`${n} - ${s&&null!==s.displayName?s.displayName:this.$tc("swag-cms-extensions.sw-cms.section.actions.pageForm.emptyAnchorName")}`}}});var y=s("rId5"),E=s.n(y);s("se01");const{Component:A}=Shopware;A.override("sw-cms-section-actions",{template:E.a,computed:{scrollNavigation(){return this.section.extensions.swagCmsExtensionsScrollNavigation},scrollNavigationPointTooltip(){return{message:`${this.$tc("swag-cms-extensions.sw-cms.section.actions.viewports.tooltipPrefix")} ${this.scrollNavigation&&this.scrollNavigation.displayName?this.scrollNavigation.displayName:this.$tc("swag-cms-extensions.sw-cms.section.actions.viewports.emptyAnchorName")}`,position:"right"}}}});const C=Object.freeze({SECTION:{EMPTY_FIELD_WHEN_ACTIVE:"SWAG_SCROLL_NAVIGATION_EMPTY_FIELD_WHEN_ACTIVE_ERROR",FIELD_TOO_LONG:"SWAG_SCROLL_NAVIGATION_FIELD_TOO_LONG_ERROR",DUPLICATE_VALUE:"SWAG_SCROLL_NAVIGATION_DUPLICATE_VALUE_ERROR"}});const{Component:L,State:T,Filter:I}=Shopware,{ShopwareError:D}=Shopware.Classes;L.override("sw-cms-detail",{inject:["repositoryFactory"],computed:{scrollNavigationRepository(){return this.repositoryFactory.create("swag_cms_extensions_scroll_navigation")},loadPageCriteria(){const e=this.$super("loadPageCriteria");return e.addAssociation("swagCmsExtensionsScrollNavigationPageSettings").getAssociation("sections").addAssociation("swagCmsExtensionsScrollNavigation").getAssociation("blocks").addAssociation("swagCmsExtensionsQuickview"),e}},methods:{onSave(){return!1===this.validateSections()?this.$super("onSave"):Promise.reject()},validateSections(){return this.occuredDisplayNames=[],Array.from(this.page.sections).some(this.validateSection)},validateSection(e){const n=this.getScrollNavigation(e);if(!n)return!1;let s="";n.displayName&&(n.displayName=n.displayName.trim(),s=n.displayName);const i=s.length>255;if(n.active&&0===s.length||i){const n=i?C.SECTION.FIELD_TOO_LONG:C.SECTION.EMPTY_FIELD_WHEN_ACTIVE;return this.commitApiError(e,"displayName",n),this.onInvalidLength(e,s),!0}return!0===n.active&&(this.occuredDisplayNames.includes(s)?(this.commitApiError(e,"displayName",C.SECTION.DUPLICATE_VALUE),this.onInvalidDisplayName(e,s),!0):(this.occuredDisplayNames.push(s),!1))},onInvalidDisplayName(e,n){const s=this.$tc("swag-cms-extensions.sw-cms.detail.scrollNavigation.duplicateDisplayNameMessage",0,{currentName:n});this.onInvalidInput(e,s)},onInvalidLength(e,n){const s=I.getByName("truncate")(n,40),i=this.$tc("swag-cms-extensions.sw-cms.detail.scrollNavigation.invalidDisplayNameLengthMessage",n.length-1,{truncatedName:s});this.onInvalidInput(e,i)},onInvalidInput(e,n){this.selectSection(e),this.createNotificationError({title:this.$tc("global.default.error"),message:n})},commitApiError(e,n,s){const i=`swag_cms_extensions_scroll_navigation.${this.getScrollNavigation(e).id}.${n}`,t=new D({code:s,detail:this.$tc("swag-cms-extensions.error-codes."+s)});T.commit("error/addApiError",{expression:i,error:t})},selectSection(e){T.dispatch("cmsPageState/setSection",e),this.$parent.$emit("page-config-open","itemConfig")},getScrollNavigation(e){return void 0!==e.extensions.swagCmsExtensionsScrollNavigation?e.extensions.swagCmsExtensionsScrollNavigation:this.scrollNavigationRepository.create(Shopware.Context.api)}}})},LW0H:function(e,n,s){var i=s("tFyS");"string"==typeof i&&(i=[[e.i,i,""]]),i.locals&&(e.exports=i.locals);(0,s("SZ7m").default)("7907d01e",i,!0,{})},QIAG:function(e,n){e.exports='{% block swag_cms_extensions_block_config %}\n    <div class="sw-cms-block-config swag-cms-extensions-block-behavior-config">\n\n        {% block swag_cms_extensions_block_config_show_quickview_field %}\n            <sw-switch-field v-model="quickview.active"\n                             :label="$tc(\'swag-cms-extensions.sw-cms.detail.quickview.showQuickviewLabel\')"\n                             :helpText="$tc(\'swag-cms-extensions.sw-cms.detail.quickview.showQuickviewHelpText\')"\n                             :disabled="!blockIsProductRelated"\n                             @change="quickviewActiveChanged">\n            </sw-switch-field>\n        {% endblock %}\n    </div>\n{% endblock %}\n'},UFuc:function(e){e.exports=JSON.parse('{"swag-cms-extensions":{"error-codes":{"SWAG_SCROLL_NAVIGATION_EMPTY_FIELD_WHEN_ACTIVE_ERROR":"Dieses Feld darf nicht leer sein, wenn der Navigationspunkt aktiv ist.","SWAG_SCROLL_NAVIGATION_FIELD_TOO_LONG_ERROR":"Die maximale Länge dieses Feldes wurde überschritten.","SWAG_SCROLL_NAVIGATION_DUPLICATE_VALUE_ERROR":"Dieses Feld hat einen Wert, der bereits verwendet wird."},"sw-cms":{"detail":{"quickview":{"showQuickviewLabel":"Quickview verwenden","showQuickviewHelpText":"Beim Klick auf produktbezogene Blöcke wird die Quickview statt der Detailseite angezeigt.","sidebarHeaderBlockBehaviorSettings":"Quickview"},"scrollNavigation":{"title":"Scroll-Navigation","anchorSwitch":"Setze Navigationspunkt","anchorTitle":"Name des Navigationspunktes","anchorTitleHelpText":"Die Navigationspunkte werden in der Storefront als navigierbare Liste der vorhandenen Sektionen ausgegeben.","duplicateDisplayNameMessage":"Dieser Navigationspunkt ist bereits vorhanden: \\"{currentName}\\".","invalidDisplayNameLengthMessage":"Mindestens einer deiner aktiven Navigationspunkte hat keinen Namen. | Der Name dieses Navigationspunktes überschreitet die Maximallänge von 255 Zeichen: \\"{truncatedName}\\"","pageSettings":{"title":"Scroll-Navigation","description":"Um animiertes Scrollen nutzen zu können, stelle sicher, dass du die dafür notwendigen Navigationspunkte aktiviert und Namen vergeben hast.","advancedSettingsLabel":"Erweiterte Einstellungen","activeLabel":"Animiertes Scrollen","activeHelpText":"Animiertes Scrollen macht die Navigation zwischen Navigationspunkten flüssiger. Für einen reibungslosen Bildlauf werden Werte zwischen 500ms und 1000ms empfohlen.","durationLabel":"Dauer","durationSuffix":"ms","easingLabel":"Scrollverhalten","easingDegreeLabel":"Grad der Verlaufskurve","easingDegreeHelpText":"Der Grad gibt die Stärke der Beschleunigung bzw. Verlangsamung an. Umso niedriger, desto gleichmäßiger der Scroll-Verlauf. Für ein intuitives Scrolling wird ein Wert zwischen 2 und 5 empfohlen. Dieser Wert wirkt sich nicht auf elastisches und lineares Scrollen aus.","bouncyLabel":"Elastisches Scrolling","easing":{"linear":"Konstant (Linear)","in":"Beschleunigen (Ease In)","out":"Verlangsamen (Ease Out)","inOut":"Beschleunigen & Verlangsamen (Ease InOut)"}}}},"sidebar":{"emptyAnchorName":"(Kein Name gesetzt)"},"section":{"actions":{"viewports":{"tooltipPrefix":"Navigationspunkt:","emptyAnchorName":"(Kein Name gesetzt)"},"pageForm":{"labelPrefix":"Navigationspunkt","emptyAnchorName":"(Kein Name gesetzt)"}}}}}}')},beRM:function(e,n,s){var i=s("xTmv");"string"==typeof i&&(i=[[e.i,i,""]]),i.locals&&(e.exports=i.locals);(0,s("SZ7m").default)("5949a8fe",i,!0,{})},gHnw:function(e,n){e.exports='{% block sw_cms_sidebar_block_layout_settings_content %}\n    {% parent %}\n\n    {% block swag_cms_extensions_sidebar_block_behavior_settings_content %}\n        <sw-sidebar-collapse :expandOnLoading="false">\n            {% block swag_cms_extensions_sidebar_block_behavior_settings_header %}\n                <template #header>\n                    <span>\n                        {{ $tc(\'swag-cms-extensions.sw-cms.detail.quickview.sidebarHeaderBlockBehaviorSettings\') }}\n                    </span>\n                </template>\n            {% endblock %}\n\n            {% block swag_cms_extensions_sidebar_block_behavior_settings_form %}\n                <template #content>\n                    <swag-cms-extensions-block-behavior-config\n                        v-model="selectedBlock">\n                    </swag-cms-extensions-block-behavior-config>\n                </template>\n            {% endblock %}\n        </sw-sidebar-collapse>\n    {% endblock %}\n{% endblock %}\n\n{% block sw_cms_sidebar_section_settings_content %}\n    {% parent %}\n\n    {% block swag_cms_extensions_sidebar_section_settings_scroll_navigation %}\n        <sw-sidebar-collapse :expandOnLoading="true">\n\n            {% block swag_cms_extensions_sidebar_section_settings_scroll_navigation_header %}\n                <template #header>\n                    <span>\n                        {{ $tc(\'swag-cms-extensions.sw-cms.detail.scrollNavigation.title\') }}\n                    </span>\n                </template>\n            {% endblock %}\n\n            {% block swag_cms_extensions_sidebar_section_settings_scroll_navigation_content %}\n                <template #content>\n                    <swag-cms-extensions-section-scroll-behavior-config\n                        v-model="selectedSection">\n                    </swag-cms-extensions-section-scroll-behavior-config>\n                </template>\n            {% endblock %}\n        </sw-sidebar-collapse>\n    {% endblock %}\n{% endblock %}\n\n{% block sw_cms_sidebar_navigator_section_header %}\n    <div :class="navigatorSectionHeaderWrapperClass(section)">\n        {% parent %}\n    </div>\n{% endblock %}\n\n{% block sw_cms_sidebar_navigator_section_menu %}\n    {% block sw_cms_sidebar_navigator_section_menu_scroll_navigation_anchor %}\n        <div v-if="scrollNavigationIsActive(section)">\n            <sw-icon v-tooltip="scrollNavigationAnchorTooltip(section)"\n                     class="navigator-section-header__scroll-navigation-anchor"\n                     name="default-action-tags"\n                     size="16">\n            </sw-icon>\n        </div>\n    {% endblock %}\n\n    {% parent %}\n{% endblock %}\n\n{% block sw_cms_sidebar_page_settings_content %}\n    {% parent %}\n\n    {% block sw_cms_sidebar_page_settings_content_scroll_navigation %}\n        <sw-sidebar-collapse :expandOnLoading="true">\n\n            {% block sw_cms_sidebar_page_settings_content_scroll_navigation_header %}\n                <template #header>\n                    <span>\n                        {{ $tc(\'swag-cms-extensions.sw-cms.detail.scrollNavigation.pageSettings.title\') }}\n                    </span>\n                </template>\n            {% endblock %}\n\n            {% block sw_cms_sidebar_page_settings_content_scroll_navigation_header_content %}\n                <template #content>\n                    <swag-cms-extensions-page-scroll-behavior-config\n                        v-model="page">\n                    </swag-cms-extensions-page-scroll-behavior-config>\n                </template>\n            {% endblock %}\n        </sw-sidebar-collapse>\n    {% endblock %}\n{% endblock %}'},jpIc:function(e,n){e.exports='{% block swag_cms_extensions_sidebar_page_scroll_behavior_config %}\n    <div class="swag-cms-page-config__scroll-navigation-page-settings">\n\n        {% block swag_cms_extensions_sidebar_page_scroll_behavior_config_smooth_scrolling %}\n            {% block swag_cms_extensions_sidebar_page_scroll_behavior_config_smooth_scrolling_description %}\n                <div class="info-text">\n                    {{ $tc(\'swag-cms-extensions.sw-cms.detail.scrollNavigation.pageSettings.description\') }}\n                </div>\n            {% endblock %}\n\n            {% block swag_cms_extensions_sidebar_page_scroll_behavior_config_smooth_scrolling_active %}\n                <sw-switch-field\n                    v-model="scrollNavigationPageSettings.active"\n                    class="scroll-navigation__smooth-scrolling-active"\n                    :label="$tc(\'swag-cms-extensions.sw-cms.detail.scrollNavigation.pageSettings.activeLabel\')"\n                    :helpText="$tc(\'swag-cms-extensions.sw-cms.detail.scrollNavigation.pageSettings.activeHelpText\')"\n                    :disabled="!pageHasNavigationPoints"\n                    @change="pageSettingsActiveChanged">\n                </sw-switch-field>\n            {% endblock %}\n\n            {% block swag_cms_extensions_sidebar_page_scroll_behavior_config_smooth_scrolling_easing %}\n                <sw-single-select\n                    v-model="scrollNavigationPageSettings.easing"\n                    :label="$tc(\'swag-cms-extensions.sw-cms.detail.scrollNavigation.pageSettings.easingLabel\')"\n                    :options="easingTypes"\n                    :disabled="!smoothScrollingActive"\n                    @change="pageSettingsActiveChanged">\n                </sw-single-select>\n            {% endblock %}\n\n            {% block swag_cms_extensions_sidebar_page_scroll_behavior_config_smooth_scrolling_duration %}\n                <sw-number-field\n                    v-model="scrollNavigationPageSettings.duration"\n                    class="scroll-navigation__smooth-scrolling-duration"\n                    :label="$tc(\'swag-cms-extensions.sw-cms.detail.scrollNavigation.pageSettings.durationLabel\')"\n                    :disabled="!smoothScrollingActive"\n                    :digits="0"\n                    @change="pageSettingsActiveChanged">\n\n                    {% block swag_cms_extensions_sidebar_page_scroll_behavior_config_smooth_scrolling_duration_suffix %}\n                        <template #suffix>\n                            <span class="scroll-navigation__smooth-scrolling-duration-suffix">\n                                {{ $tc(\'swag-cms-extensions.sw-cms.detail.scrollNavigation.pageSettings.durationSuffix\') }}\n                            </span>\n                        </template>\n                    {% endblock %}\n                </sw-number-field>\n            {% endblock %}\n\n            {% block swag_cms_extensions_sidebar_page_scroll_behavior_config_smooth_scrolling_advanced %}\n                <sw-sidebar-collapse\n                    class="scroll-navigation__smooth-scrolling-advanced-settings"\n                    :expandOnLoading="false">\n\n                    {% block swag_cms_extensions_sidebar_page_scroll_behavior_config_smooth_scrolling_advanced_header %}\n                        <template #header>\n                            {{ $tc(\'swag-cms-extensions.sw-cms.detail.scrollNavigation.pageSettings.advancedSettingsLabel\') }}\n                        </template>\n                    {% endblock %}\n\n                    {% block swag_cms_extensions_sidebar_page_scroll_behavior_config_smooth_scrolling_advanced_content %}\n                        <template #content>\n\n                            {% block swag_cms_extensions_sidebar_page_scroll_behavior_config_smooth_scrolling_bouncy %}\n                                <sw-switch-field\n                                    v-model="scrollNavigationPageSettings.bouncy"\n                                    class="scroll-navigation__smooth-scrolling-bouncy"\n                                    :label="$tc(\'swag-cms-extensions.sw-cms.detail.scrollNavigation.pageSettings.bouncyLabel\')"\n                                    :disabled="!smoothScrollingActive || scrollNavigationPageSettings.easing === \'linear\'"\n                                    @change="pageSettingsActiveChanged">\n                                </sw-switch-field>\n                            {% endblock %}\n\n                            {% block swag_cms_extensions_sidebar_page_scroll_behavior_config_smooth_scrolling_easing_degree %}\n                                {# ToDo PT-11794 - Re-enable helptext and remove tooltip #}\n                                <sw-number-field\n                                    v-model="scrollNavigationPageSettings.easingDegree"\n                                    class="scroll-navigation__smooth-scrolling-easing-degree"\n                                    :label="$tc(\'swag-cms-extensions.sw-cms.detail.scrollNavigation.pageSettings.easingDegreeLabel\')"\n                                    :disabled="easingDegreeDisabled"\n{#                                    :helpText="$tc(\'swag-cms-extensions.sw-cms.detail.scrollNavigation.pageSettings.easingDegreeHelpText\')"#}\n                                    v-tooltip="{\n                                        message: $tc(\'swag-cms-extensions.sw-cms.detail.scrollNavigation.pageSettings.easingDegreeHelpText\')\n                                    }"\n                                    :digits="0"\n                                    :min="2"\n                                    :max="32"\n                                    @change="pageSettingsActiveChanged">\n                                </sw-number-field>\n                            {% endblock %}\n                        </template>\n                    {% endblock %}\n                </sw-sidebar-collapse>\n            {% endblock %}\n        {% endblock %}\n    </div>\n{% endblock %}'},qeqr:function(e,n){e.exports='{% block swag_cms_extensions_sidebar_section_scroll_behavior_config %}\n    <div class="swag-cms-section-config__scroll-navigation">\n\n        {% block swag_cms_extensions_sidebar_section_scroll_behavior_config_anchor_switch %}\n            <sw-switch-field\n                v-model="swagCmsExtensionsScrollNavigation.active"\n                class="scroll-navigation__anchor-switch"\n                :label="$tc(\'swag-cms-extensions.sw-cms.detail.scrollNavigation.anchorSwitch\')"\n                @change="scrollNavigationActiveChanged">\n            </sw-switch-field>\n        {% endblock %}\n\n        {% block swag_cms_extensions_sidebar_section_scroll_behavior_config_anchor_title %}\n            <sw-text-field\n                v-model="swagCmsExtensionsScrollNavigation.displayName"\n                class="scroll-navigation__anchor-title"\n                :label="$tc(\'swag-cms-extensions.sw-cms.detail.scrollNavigation.anchorTitle\')"\n                :helpText="$tc(\'swag-cms-extensions.sw-cms.detail.scrollNavigation.anchorTitleHelpText\')"\n                :error="swagCmsExtensionsScrollNavigationDisplayNameError"\n                @change="scrollNavigationActiveChanged">\n            </sw-text-field>\n        {% endblock %}\n    </div>\n{% endblock %}'},rId5:function(e,n){e.exports='{% block sw_cms_section_action_select %}\n    {% parent %}\n\n    {% block sw_cms_section_action_scroll_navigation_label %}\n        <div v-if="scrollNavigation && scrollNavigation.active"\n             class="sw-cms-section__action sw-cms-section-scroll-navigation-label">\n            <sw-icon v-tooltip="scrollNavigationPointTooltip"\n                     name="default-action-tags"\n                     size="20">\n            </sw-icon>\n        </div>\n    {% endblock %}\n{% endblock %}\n'},se01:function(e,n,s){var i=s("Ew5G");"string"==typeof i&&(i=[[e.i,i,""]]),i.locals&&(e.exports=i.locals);(0,s("SZ7m").default)("4d2e210d",i,!0,{})},suCZ:function(e,n,s){},tFyS:function(e,n,s){},vFYS:function(e,n,s){var i=s("suCZ");"string"==typeof i&&(i=[[e.i,i,""]]),i.locals&&(e.exports=i.locals);(0,s("SZ7m").default)("4af68e52",i,!0,{})},wEWP:function(e,n,s){},xTmv:function(e,n,s){}},[["JC3F","runtime","vendors-node"]]]);