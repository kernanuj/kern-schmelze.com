(this.webpackJsonp=this.webpackJsonp||[]).push([["inv-advanced-cms-elements"],{"44ER":function(e,t){e.exports='{% block sw_cms_element_image_config %}\n    <div class="sw-cms-el-config-two-cols-text-image">\n        <sw-field class="inv-two-cols-text-image-field"\n                  v-model="element.config.imageSrc.value"\n                  type="image"\n                  label="Image Source"\n                  placeholder="Select image">\n        </sw-field>\n        <sw-field class="sw-cms-el-config-two-cols-text-image__text"\n                  v-model="element.config.content.value"\n                  type="textarea"\n                  label="Text"\n                  placeholder="Lorem ipsum ...">\n        </sw-field>\n    </div>\n{% endblock %}\n'},"5o1d":function(e,t,s){var o=s("iU3b");"string"==typeof o&&(o=[[e.i,o,""]]),o.locals&&(e.exports=o.locals);(0,s("SZ7m").default)("0185231f",o,!0,{})},A2YA:function(e,t){e.exports='{% block sw_cms_element_two-cols-text-image_preview %}\n    <div class="sw-cms-el-preview-two-cols-text-image">\n        <img class="sw-cms-el-preview-two-cols-text-image-img" :src="\'/administration/static/img/cms/preview_mountain_small.jpg\' | asset">\n\n        <sw-icon class="sw-cms-el-preview-two-cols-text-image-play" name="multicolor-action-play"></sw-icon>\n    </div>\n{% endblock %}\n'},GSPM:function(e,t,s){var o=s("Giyv");"string"==typeof o&&(o=[[e.i,o,""]]),o.locals&&(e.exports=o.locals);(0,s("SZ7m").default)("5e691694",o,!0,{})},Giyv:function(e,t,s){},Jts4:function(e){e.exports=JSON.parse('{"sw-cms":{"elements":{"customTextImageElement":{"label":"2 Spalten Text und Bild"}}}}')},iU3b:function(e,t,s){},n43m:function(e,t,s){"use strict";s.r(t);var o=s("yjbU"),n=s.n(o);s("5o1d");Shopware.Component.register("sw-cms-el-two-cols-text-image",{template:n.a,mixins:[Mixin.getByName("cms-element")],computed:{imageSrc(){return this.element.config.imageSrc.value}},created(){this.createdComponent()},methods:{createdComponent(){this.initElementConfig("two-cols-text-image")}}});var a=s("44ER"),i=s.n(a);const{Component:m,Mixin:l}=Shopware;m.register("sw-cms-el-config-two-cols-text-image",{template:i.a,mixins:[l.getByName("cms-element")],created(){this.createdComponent()},methods:{createdComponent(){this.initElementConfig("two-cols-text-image")}}});var c=s("A2YA"),r=s.n(c);s("GSPM");Shopware.Component.register("sw-cms-el-preview-two-cols-text-image",{template:r.a}),Shopware.Service("cmsService").registerCmsElement({name:"two-cols-text-image",label:"sw-cms.elements.customTextImageElement.label",component:"sw-cms-el-two-cols-text-image",configComponent:"sw-cms-el-config-two-cols-text-image",previewComponent:"sw-cms-el-preview-two-cols-text-image",defaultConfig:{content:{source:"static",value:"\n                <h2>Lorem Ipsum dolor sit amet</h2>\n                <p>Lorem ipsum dolor sit amet, consetetur sadipscing elitr,\n                sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat,\n                sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum.\n                Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.\n                Lorem ipsum dolor sit amet, consetetur sadipscing elitr,\n                sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.\n                At vero eos et accusam et justo duo dolores et ea rebum.\n                Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.</p>\n            ".trim()},imageSrc:{source:"static",value:null,required:!0,entity:{name:"imageSrc"}},displayMode:{source:"static",value:"standard"},url:{source:"static",value:null},newTab:{source:"static",value:!1},minHeight:{source:"static",value:"340px"},verticalAlign:{source:"static",value:null}}});var u=s("Jts4"),d=s("yasC");Shopware.Locale.extend("de-DE",u),Shopware.Locale.extend("en-GB",d)},yasC:function(e){e.exports=JSON.parse('{"sw-cms":{"elements":{"customTextImageElement":{"label":"2 Columns Text and Image"}}}}')},yjbU:function(e,t){e.exports='{% block sw_cms_element_two_cols_text_image %}\n    <div class="sw-cms-el-two-cols-text-image">\n        <img :src="media"></img>\n    </div>\n{% endblock %}\n'}},[["n43m","runtime","vendors-node"]]]);