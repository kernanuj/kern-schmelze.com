import './module/sw-cms/blocks/text-image/image-text-gallery-four-columns';
import './module/sw-cms/blocks/text-image/image-text-gallery-six-columns';
import './module/sw-cms/blocks/text-image/text-on-image-two-columns';
import './module/sw-cms/blocks/text-image/image-text-row-four-columns';
import './module/sw-cms/blocks/text-image/image-text-row-two-columns';
import './module/sw-cms/blocks/commerce/product-four-column';
import './module/sw-cms/blocks/commerce/product-two-column';

import deDE from './module/sw-cms/snippet/de-DE.json';
import enGB from './module/sw-cms/snippet/en-GB.json';

Shopware.Locale.extend('de-DE', deDE);
Shopware.Locale.extend('en-GB', enGB);
