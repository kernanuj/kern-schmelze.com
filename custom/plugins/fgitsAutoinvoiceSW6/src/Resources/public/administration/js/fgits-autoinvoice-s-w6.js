(this.webpackJsonp=this.webpackJsonp||[]).push([["fgits-autoinvoice-s-w6"],{CkOj:function(e,t,n){"use strict";n.d(t,"a",(function(){return u}));var i=n("lSNA"),r=n.n(i),o=n("lO2t"),s=n("lYO9");function a(e,t){var n=Object.keys(e);if(Object.getOwnPropertySymbols){var i=Object.getOwnPropertySymbols(e);t&&(i=i.filter((function(t){return Object.getOwnPropertyDescriptor(e,t).enumerable}))),n.push.apply(n,i)}return n}function c(e){for(var t=1;t<arguments.length;t++){var n=null!=arguments[t]?arguments[t]:{};t%2?a(Object(n),!0).forEach((function(t){r()(e,t,n[t])})):Object.getOwnPropertyDescriptors?Object.defineProperties(e,Object.getOwnPropertyDescriptors(n)):a(Object(n)).forEach((function(t){Object.defineProperty(e,t,Object.getOwnPropertyDescriptor(n,t))}))}return e}function u(e){var t=function(e){var t;if(o.a.isString(e))try{t=JSON.parse(e)}catch(e){return!1}else{if(!o.a.isObject(e)||o.a.isArray(e))return!1;t=e}return t}(e);if(!t)return null;if(!0===t.parsed||!function(e){return void 0!==e.data||void 0!==e.errors||void 0!==e.links||void 0!==e.meta}(t))return t;var n=function(e){var t={links:null,errors:null,data:null,associations:null,aggregations:null};if(e.errors)return t.errors=e.errors,t;var n=function(e){var t=new Map;if(!e||!e.length)return t;return e.forEach((function(e){var n="".concat(e.type,"-").concat(e.id);t.set(n,e)})),t}(e.included);if(o.a.isArray(e.data))t.data=e.data.map((function(e){var i=l(e,n);return Object(s.f)(i,"associationLinks")&&(t.associations=c({},t.associations,{},i.associationLinks),delete i.associationLinks),i}));else if(o.a.isObject(e.data)){var i=l(e.data,n);Object.prototype.hasOwnProperty.call(i,"associationLinks")&&(t.associations=c({},t.associations,{},i.associationLinks),delete i.associationLinks),t.data=i}else t.data=null;e.meta&&Object.keys(e.meta).length&&(t.meta=d(e.meta));e.links&&Object.keys(e.links).length&&(t.links=e.links);e.aggregations&&Object.keys(e.aggregations).length&&(t.aggregations=e.aggregations);return t}(t);return n.parsed=!0,n}function l(e,t){var n={id:e.id,type:e.type,links:e.links||{},meta:e.meta||{}};e.attributes&&Object.keys(e.attributes).length>0&&(n=c({},n,{},d(e.attributes)));if(e.relationships){var i=function(e,t){var n={},i={};return Object.keys(e).forEach((function(r){var s=e[r];if(s.links&&Object.keys(s.links).length&&(i[r]=s.links.related),s.data){var a=s.data;o.a.isArray(a)?n[r]=a.map((function(e){return p(e,t)})):o.a.isObject(a)?n[r]=p(a,t):n[r]=null}})),{mappedRelations:n,associationLinks:i}}(e.relationships,t);n=c({},n,{},i.mappedRelations,{},{associationLinks:i.associationLinks})}return n}function d(e){var t={};return Object.keys(e).forEach((function(n){var i=e[n],r=n.replace(/-([a-z])/g,(function(e,t){return t.toUpperCase()}));t[r]=i})),t}function p(e,t){var n="".concat(e.type,"-").concat(e.id);return t.has(n)?l(t.get(n),t):e}},GDeu:function(e,t,n){},J1cG:function(e,t){e.exports='{% block fgits_orderdetail_payment_exclude %}\n<sw-multi-select\n    v-if="options.length"\n    :label="getTitle"\n    :helpText="getHelpText"\n    :options="options"\n    :isLoading="isLoading"\n    valueProperty="id"\n    labelProperty="name"\n    v-model="currentValue"\n>\n</sw-multi-select>\n{% endblock %}\n'},"JR/P":function(e,t){e.exports='{% block fgits_orderdetail_country_select %}\n<sw-single-select\n    v-if="options.length"\n    :label="getTitle"\n    :options="options"\n    :isLoading="isLoading"\n    valueProperty="id"\n    labelProperty="name"\n    v-model="currentValue"\n>\n</sw-single-select>\n{% endblock %}\n'},KWLg:function(e,t){e.exports='{% block fgits_orderdetail_state_select %}\n<sw-multi-select\n    v-if="options.length"\n    :label="$tc(\'fgits-autoinvoice.state.select.label\')"\n    :helpText="$tc(\'fgits-autoinvoice.state.select.helpText\')"\n    :options="options"\n    :isLoading="isLoading"\n    valueProperty="id"\n    labelProperty="name"\n    v-model="currentValue"\n>\n</sw-multi-select>\n{% endblock %}\n'},LdID:function(e,t){e.exports='{% block fgits_orderdetail_autoinvoice_send %}\n    <sw-button-process\n            size="x-small"\n            :variant="variant"\n            @click="fgitsAutoinvoiceSend"\n            :processSuccess="sent"\n            animationTimeout="1250"\n            :isLoading="isLoading">\n        {{ text }}\n    </sw-button-process>\n{% endblock %}\n'},SMhS:function(e,t){e.exports='{% block fgits_orderdetail_autoinvoice_processed %}\n    <sw-checkbox-field\n            :label="$tc(\'fgits-autoinvoice.processed.checkbox.label\')"\n            v-model="fgitsAutoinvoiceProcessed"\n            disabled="true">\n    </sw-checkbox-field>\n{% endblock %}\n'},SwLI:function(e,t,n){"use strict";n.r(t);var i=n("lwsE"),r=n.n(i),o=n("W8MJ"),s=n.n(o),a=n("CkOj"),c=function(){function e(t,n,i){var o=arguments.length>3&&void 0!==arguments[3]?arguments[3]:"application/vnd.api+json";r()(this,e),this.httpClient=t,this.loginService=n,this.apiEndpoint=i,this.contentType=o}return s()(e,[{key:"getList",value:function(t){var n=t.page,i=void 0===n?1:n,r=t.limit,o=void 0===r?25:r,s=t.sortBy,a=t.sortDirection,c=void 0===a?"asc":a,u=t.sortings,l=t.queries,d=t.term,p=t.criteria,h=t.aggregations,g=t.associations,f=t.headers,v=t.versionId,m=t.ids,y=t["total-count-mode"],b=void 0===y?0:y,w=this.getBasicHeaders(f),k={page:i,limit:o};return u?k.sort=u:s&&s.length&&(k.sort=("asc"===c.toLowerCase()?"":"-")+s),m&&(k.ids=m.join("|")),d&&(k.term=d),p&&(k.filter=[p.getQuery()]),h&&(k.aggregations=h),g&&(k.associations=g),v&&(w=Object.assign(w,e.getVersionHeader(v))),l&&(k.query=l),b&&(k["total-count-mode"]=b),k.term&&k.term.length||k.filter&&k.filter.length||k.aggregations||k.sort||k.queries||k.associations?this.httpClient.post("".concat(this.getApiBasePath(null,"search")),k,{headers:w}).then((function(t){return e.handleResponse(t)})):this.httpClient.get(this.getApiBasePath(),{params:k,headers:w}).then((function(t){return e.handleResponse(t)}))}},{key:"getById",value:function(t){var n=arguments.length>1&&void 0!==arguments[1]?arguments[1]:{},i=arguments.length>2&&void 0!==arguments[2]?arguments[2]:{};if(!t)return Promise.reject(new Error("Missing required argument: id"));var r=n,o=this.getBasicHeaders(i);return this.httpClient.get(this.getApiBasePath(t),{params:r,headers:o}).then((function(t){return e.handleResponse(t)}))}},{key:"updateById",value:function(t,n){var i=arguments.length>2&&void 0!==arguments[2]?arguments[2]:{},r=arguments.length>3&&void 0!==arguments[3]?arguments[3]:{};if(!t)return Promise.reject(new Error("Missing required argument: id"));var o=i,s=this.getBasicHeaders(r);return this.httpClient.patch(this.getApiBasePath(t),n,{params:o,headers:s}).then((function(t){return e.handleResponse(t)}))}},{key:"deleteAssociation",value:function(e,t,n,i){if(!e||!n||!n)return Promise.reject(new Error("Missing required arguments."));var r=this.getBasicHeaders(i);return this.httpClient.delete("".concat(this.getApiBasePath(e),"/").concat(t,"/").concat(n),{headers:r}).then((function(e){return e.status>=200&&e.status<300?Promise.resolve(e):Promise.reject(e)}))}},{key:"create",value:function(t){var n=arguments.length>1&&void 0!==arguments[1]?arguments[1]:{},i=arguments.length>2&&void 0!==arguments[2]?arguments[2]:{},r=n,o=this.getBasicHeaders(i);return this.httpClient.post(this.getApiBasePath(),t,{params:r,headers:o}).then((function(t){return e.handleResponse(t)}))}},{key:"delete",value:function(e){var t=arguments.length>1&&void 0!==arguments[1]?arguments[1]:{},n=arguments.length>2&&void 0!==arguments[2]?arguments[2]:{};if(!e)return Promise.reject(new Error("Missing required argument: id"));var i=Object.assign({},t),r=this.getBasicHeaders(n);return this.httpClient.delete(this.getApiBasePath(e),{params:i,headers:r})}},{key:"clone",value:function(t){return t?this.httpClient.post("/_action/clone/".concat(this.apiEndpoint,"/").concat(t),null,{headers:this.getBasicHeaders()}).then((function(t){return e.handleResponse(t)})):Promise.reject(new Error("Missing required argument: id"))}},{key:"versionize",value:function(e){var t=arguments.length>1&&void 0!==arguments[1]?arguments[1]:{},n=arguments.length>2&&void 0!==arguments[2]?arguments[2]:{},i="/_action/version/".concat(this.apiEndpoint,"/").concat(e),r=Object.assign({},t),o=this.getBasicHeaders(n);return this.httpClient.post(i,{},{params:r,headers:o})}},{key:"mergeVersion",value:function(t,n,i,r){if(!t)return Promise.reject(new Error("Missing required argument: id"));if(!n)return Promise.reject(new Error("Missing required argument: versionId"));var o=Object.assign({},i),s=Object.assign(e.getVersionHeader(n),this.getBasicHeaders(r)),a="_action/version/merge/".concat(this.apiEndpoint,"/").concat(n);return this.httpClient.post(a,{},{params:o,headers:s})}},{key:"getApiBasePath",value:function(e){var t=arguments.length>1&&void 0!==arguments[1]?arguments[1]:"",n="";return t&&t.length&&(n+="".concat(t,"/")),e&&e.length>0?"".concat(n).concat(this.apiEndpoint,"/").concat(e):"".concat(n).concat(this.apiEndpoint)}},{key:"getBasicHeaders",value:function(){var e=arguments.length>0&&void 0!==arguments[0]?arguments[0]:{},t={Accept:this.contentType,Authorization:"Bearer ".concat(this.loginService.getToken()),"Content-Type":"application/json"};return Object.assign({},t,e)}},{key:"apiEndpoint",get:function(){return this.endpoint},set:function(e){this.endpoint=e}},{key:"httpClient",get:function(){return this.client},set:function(e){this.client=e}},{key:"contentType",get:function(){return this.type},set:function(e){this.type=e}}],[{key:"handleResponse",value:function(t){if(null===t.data||void 0===t.data)return t;var n=t.data,i=t.headers;return i&&i["content-type"]&&"application/vnd.api+json"===i["content-type"]&&(n=e.parseJsonApiData(n)),n}},{key:"parseJsonApiData",value:function(e){return Object(a.a)(e)}},{key:"getVersionHeader",value:function(e){return{"sw-version-id":e}}}]),e}();t.default=c},"WVM/":function(e,t){e.exports='{% block sw_order_detail_base_overview_status_change %}\n    {% parent %}\n\n    <sw-container v-if="!isEditing"\n                  slot="additional-actions"\n                  columns="minmax(min-content, 1fr) minmax(min-content, 1fr) 1fr"\n                  gap="0 15px">\n\n        <div></div>\n\n        <div class="fgits-orderdetail-autoinvoice-processed">\n            <fgits-orderdetail-autoinvoice-processed\n                    :order="order"\n            >\n            </fgits-orderdetail-autoinvoice-processed>\n        </div>\n\n        <div class="fgits-orderdetail-autoinvoice-send">\n            <fgits-orderdetail-autoinvoice-send\n                    :order="order"\n            >\n            </fgits-orderdetail-autoinvoice-send>\n        </div>\n    </sw-container>\n{% endblock %}\n'},YkfD:function(e,t){e.exports='{% block fgits_orderdetail_customergroup_exclude %}\n<sw-multi-select\n    v-if="options.length"\n    :label="$tc(\'fgits-autoinvoice.customergroupExclude.select.label\')"\n    :helpText="$tc(\'fgits-autoinvoice.customergroupExclude.select.helpText\')"\n    :options="options"\n    :isLoading="isLoading"\n    valueProperty="id"\n    labelProperty="name"\n    v-model="currentValue"\n>\n</sw-multi-select>\n{% endblock %}\n'},hqRz:function(e,t){e.exports='{% block fgits_orderdetail_payment_select %}\n<sw-multi-select\n    v-if="options.length"\n    :label="$tc(\'fgits-autoinvoice.payment.select.label\')"\n    :helpText="$tc(\'fgits-autoinvoice.payment.select.helpText\')"\n    :options="options"\n    :isLoading="isLoading"\n    valueProperty="id"\n    labelProperty="name"\n    v-model="currentValue"\n>\n</sw-multi-select>\n{% endblock %}\n'},lO2t:function(e,t,n){"use strict";n.d(t,"b",(function(){return S}));var i=n("GoyQ"),r=n.n(i),o=n("YO3V"),s=n.n(o),a=n("E+oP"),c=n.n(a),u=n("wAXd"),l=n.n(u),d=n("Z0cm"),p=n.n(d),h=n("lSCD"),g=n.n(h),f=n("YiAA"),v=n.n(f),m=n("4qC0"),y=n.n(m),b=n("Znm+"),w=n.n(b),k=n("Y+p1"),O=n.n(k),j=n("UB5X"),C=n.n(j);function S(e){return void 0===e}t.a={isObject:r.a,isPlainObject:s.a,isEmpty:c.a,isRegExp:l.a,isArray:p.a,isFunction:g.a,isDate:v.a,isString:y.a,isBoolean:w.a,isEqual:O.a,isNumber:C.a,isUndefined:S}},lPLF:function(e,t,n){"use strict";n.r(t);var i=n("xkBY"),r=n.n(i);const{Component:o}=Shopware;o.register("fgits-orderdetail-autoinvoice-cron",{template:r.a,inject:["fgitsAutoinvoiceService"],props:{value:{type:Boolean,required:!1,default:()=>!1}},computed:{currentValue:{get(){return!!this.value&&this.value},set(e){this.activateCron(),this.$emit("input",e),this.$emit("change",e)}}},methods:{activateCron:async function(){await this.fgitsAutoinvoiceService.activateCron()}}});var s=n("hqRz"),a=n.n(s);const{Component:c}=Shopware,{Criteria:u}=Shopware.Data;c.register("fgits-orderdetail-payment-select",{template:a.a,inject:["repositoryFactory"],props:{value:{type:Array,required:!1,default:()=>[]}},data:()=>({isLoading:!1,options:[]}),computed:{repository(){return this.repositoryFactory.create("state_machine_state")},currentValue:{get(){return this.value?this.value:[]},set(e){this.$emit("input",e),this.$emit("change",e)}}},created(){this.createdComponent()},methods:{createdComponent(){const e=new u;e.addSorting(u.sort("name","ASC")),e.addAssociation("stateMachine"),e.addFilter(u.equals("state_machine_state.stateMachine.technicalName","order_transaction.state")),this.isLoading=!0,this.repository.search(e,Shopware.Context.api).then(e=>{e.forEach(e=>{this.options.push(e)}),this.isLoading=!1})}}});var l=n("KWLg"),d=n.n(l);const{Component:p}=Shopware,{Criteria:h}=Shopware.Data;p.register("fgits-orderdetail-state-select",{template:d.a,inject:["repositoryFactory"],props:{value:{type:Array,required:!1,default:()=>[]}},data:()=>({isLoading:!1,options:[]}),computed:{repository(){return this.repositoryFactory.create("state_machine_state")},currentValue:{get(){return this.value?this.value:[]},set(e){this.$emit("input",e),this.$emit("change",e)}}},created(){this.createdComponent()},methods:{createdComponent(){const e=new h;e.addSorting(h.sort("name","ASC")),e.addAssociation("stateMachine"),e.addFilter(h.equals("state_machine_state.stateMachine.technicalName","order.state")),this.isLoading=!0,this.repository.search(e,Shopware.Context.api).then(e=>{e.forEach(e=>{this.options.push(e)}),this.isLoading=!1})}}});var g=n("YkfD"),f=n.n(g);const{Component:v}=Shopware,{Criteria:m}=Shopware.Data;v.register("fgits-orderdetail-customergroup-exclude",{template:f.a,inject:["repositoryFactory"],props:{value:{type:Array,required:!1,default:()=>[]}},data:()=>({isLoading:!1,options:[]}),computed:{repository(){return this.repositoryFactory.create("customer_group")},currentValue:{get(){return this.value?this.value:[]},set(e){this.$emit("input",e),this.$emit("change",e)}}},created(){this.createdComponent()},methods:{createdComponent(){const e=new m;e.addSorting(m.sort("name","ASC")),this.isLoading=!0,this.repository.search(e,Shopware.Context.api).then(e=>{e.forEach(e=>{this.options.push(e)}),this.isLoading=!1})}}});var y=n("J1cG"),b=n.n(y);const{Component:w}=Shopware,{Criteria:k}=Shopware.Data;w.register("fgits-orderdetail-payment-exclude",{template:b.a,inject:["repositoryFactory"],props:{value:{type:Array,required:!1,default:()=>[]}},data:()=>({isLoading:!1,options:[]}),computed:{repository(){return this.repositoryFactory.create("payment_method")},currentValue:{get(){return this.value?this.value:[]},set(e){this.$emit("input",e),this.$emit("change",e)}},getTitle(){return this.$attrs.title},getHelpText(){return this.$attrs.helpText}},created(){this.createdComponent()},methods:{createdComponent(){const e=new k;e.addSorting(k.sort("name","ASC")),this.isLoading=!0,this.repository.search(e,Shopware.Context.api).then(e=>{e.forEach(e=>{this.options.push(e)}),this.isLoading=!1})}}});var O=n("JR/P"),j=n.n(O);const{Component:C}=Shopware,{Criteria:S}=Shopware.Data;C.register("fgits-orderdetail-country-select",{template:j.a,inject:["repositoryFactory"],props:{value:{type:Array,required:!1,default:()=>[]}},data:()=>({isLoading:!1,options:[]}),computed:{repository(){return this.repositoryFactory.create("country")},currentValue:{get(){return this.value?this.value:[]},set(e){this.$emit("input",e),this.$emit("change",e)}},getTitle(){return this.$attrs.title}},created(){this.createdComponent()},methods:{createdComponent(){const e=new S;e.addSorting(S.sort("name","ASC")),this.isLoading=!0,this.repository.search(e,Shopware.Context.api).then(e=>{e.forEach(e=>{this.options.push(e)}),this.isLoading=!1})}}});var P=n("SMhS"),L=n.n(P);const{Component:x}=Shopware;x.register("fgits-orderdetail-autoinvoice-processed",{template:L.a,props:{order:{type:Object,required:!0}},computed:{fgitsAutoinvoiceProcessed:{get(){return!!this.order.customFields&&this.order.customFields.fgits_autoinvoice_processed}}}});var A=n("LdID"),_=n.n(A);const{Component:E}=Shopware;E.register("fgits-orderdetail-autoinvoice-send",{template:_.a,inject:["fgitsAutoinvoiceService"],props:{order:{type:Object,required:!0}},data(){return{isLoading:!1,sent:!1,text:this.$tc("fgits-autoinvoice.send.button.text"),variant:""}},methods:{fgitsAutoinvoiceSend:async function(){this.isLoading=!0,await this.fgitsAutoinvoiceService.sendInvoice(this.order.id).then(e=>{this.status=e.status}),"OK"===this.status?(this.sent=!0,this.variant="",this.isLoading=!1,window.setTimeout((function(){return window.location.reload()}),500)):(this.sent=!1,this.text=this.$tc("fgits-autoinvoice.send.button.error"),this.variant="danger",this.isLoading=!1,console.log("[#fgits-orderdetail-autoinvoice-send] "+this.status))}}});n("rDzv");var B=n("WVM/"),D=n.n(B);const{Component:$}=Shopware;$.override("sw-order-detail-base",{template:D.a});var q=n("SwLI");class T extends q.default{constructor(e,t,n="fgits_autoinvoice"){super(e,t,n)}activateCron(){return this.httpClient.get("/fgits/cron/activate",{headers:this.getBasicHeaders()})}sendInvoice(e){const t=`/fgits/order/${e}/invoice/send`;return this.httpClient.get(t,{headers:this.getBasicHeaders()}).then(e=>q.default.handleResponse(e))}}var V=T;const{Application:F}=Shopware;F.addServiceProvider("fgitsAutoinvoiceService",e=>{const t=F.getContainer("init");return new V(t.httpClient,e.loginService)})},lYO9:function(e,t,n){"use strict";n.d(t,"g",(function(){return v})),n.d(t,"a",(function(){return m})),n.d(t,"c",(function(){return y})),n.d(t,"h",(function(){return b})),n.d(t,"f",(function(){return w})),n.d(t,"b",(function(){return k})),n.d(t,"e",(function(){return O})),n.d(t,"d",(function(){return j}));var i=n("lSNA"),r=n.n(i),o=n("QkVN"),s=n.n(o),a=n("BkRI"),c=n.n(a),u=n("mwIZ"),l=n.n(u),d=n("D1y2"),p=n.n(d),h=n("lO2t");function g(e,t){var n=Object.keys(e);if(Object.getOwnPropertySymbols){var i=Object.getOwnPropertySymbols(e);t&&(i=i.filter((function(t){return Object.getOwnPropertyDescriptor(e,t).enumerable}))),n.push.apply(n,i)}return n}function f(e){for(var t=1;t<arguments.length;t++){var n=null!=arguments[t]?arguments[t]:{};t%2?g(Object(n),!0).forEach((function(t){r()(e,t,n[t])})):Object.getOwnPropertyDescriptors?Object.defineProperties(e,Object.getOwnPropertyDescriptors(n)):g(Object(n)).forEach((function(t){Object.defineProperty(e,t,Object.getOwnPropertyDescriptor(n,t))}))}return e}s.a,c.a,l.a,p.a;var v=s.a,m=c.a,y=l.a,b=p.a;function w(e,t){return Object.prototype.hasOwnProperty.call(e,t)}function k(){var e=arguments.length>0&&void 0!==arguments[0]?arguments[0]:{};return JSON.parse(JSON.stringify(e))}function O(e,t){return e===t?{}:h.a.isObject(e)&&h.a.isObject(t)?h.a.isDate(e)||h.a.isDate(t)?e.valueOf()===t.valueOf()?{}:t:Object.keys(t).reduce((function(n,i){if(!w(e,i))return f({},n,r()({},i,t[i]));if(h.a.isArray(t[i])){var o=j(e[i],t[i]);return Object.keys(o).length>0?f({},n,r()({},i,t[i])):n}if(h.a.isObject(t[i])){var s=O(e[i],t[i]);return!h.a.isObject(s)||Object.keys(s).length>0?f({},n,r()({},i,s)):n}return e[i]!==t[i]?f({},n,r()({},i,t[i])):n}),{}):t}function j(e,t){if(e===t)return[];if(!h.a.isArray(e)||!h.a.isArray(t))return t;if(e.length<=0&&t.length<=0)return[];if(e.length!==t.length)return t;if(!h.a.isObject(t[0]))return t.filter((function(t){return!e.includes(t)}));var n=[];return t.forEach((function(i,r){var o=O(e[r],t[r]);Object.keys(o).length>0&&n.push(t[r])})),n}},rDzv:function(e,t,n){var i=n("GDeu");"string"==typeof i&&(i=[[e.i,i,""]]),i.locals&&(e.exports=i.locals);(0,n("SZ7m").default)("457322b1",i,!0,{})},xkBY:function(e,t){e.exports='{% block fgits_orderdetail_autoinvoice_cron %}\n    <sw-switch-field\n            bordered\n            :label="$tc(\'fgits-autoinvoice.cron.switch.label\')"\n            :helpText="$tc(\'fgits-autoinvoice.cron.switch.helpText\')"\n            v-model="currentValue">\n    </sw-switch-field>\n{% endblock %}\n'}},[["lPLF","runtime","vendors-node"]]]);