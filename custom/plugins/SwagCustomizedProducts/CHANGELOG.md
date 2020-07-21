# 2.3.0
- PT-11308 - Removed numberfield configuration for "decimal places", which was in contradiction to the "step size" configuration
- PT-11310 - Time and date validation for the option types date field and time selection field implemented
- PT-11370 - In storefront by clicking on an image in the image selection it will be displayed in a full screen view 
- PT-11452 - Implemented optional option collapsing, if the current option is valid and clicking into a new one
- PT-11621 - Failed orders which contain one or more Custom Products can now be edited in the account
- PT-11719 - Duplicating the product template is deactivated if the template has not yet been saved.
- PT-11775 - Required options of the type file and image upload no longer block ordering a Custom Product
- PT-11823 - The HTML editor is now correctly vertical aligned in step by step mode
- PT-11840 - The "add product" button is back in the order module
- PT-11881 - Compatibility for Safari and Internet Explorer 11
- PT-11897 - File and image upload now return a correct error state when exceeding max file size or amount 

# 2.2.0
- PT-11172 - Adjusted Custom Products option modal box to fixed size
- PT-11288 - Relative surcharges are now listed correctly in the order confirmation mail
- PT-11303 - The calculated custom product price, as well as the summary of the selected options, will be broken down right above the buy button
- PT-11312 - Required options of the custom product are automatically unfolded on the product detail page
- PT-11359 - One time relative surcharges are now calculated correctly
- PT-11466 - Ensures compatibility between promotions and Customized Products
- PT-11632 - Added navigation for elements that cannot be combined due to an exclusion
- PT-11773 - Custom Products with unfilled required options can no longer be placed in the cart
- PT-11774 - The step by step mode no longer cuts off the configuration for image and file uploads
- PT-11868 - Implemented multi select validation for step-by-step mode

# 2.1.0
- PT-11476 - Provides Store API endpoints
- PT-11799 - Customer without orders can use the overview again

# 2.0.0
- PT-11724 - Deletes storefront uploads on uninstall
- PT-11743 - Ensures the extensibility of the SalesChannelProductCriteria

# 1.3.3
- PT-11698 - Adjusted max display amount of options' selections
- PT-11738 - Fixes uploads in the storefront
- PT-11739 - Fixes plugin installation for shops where the languages German and/or English are missing

# 1.3.2
- PT-11427 - Fixes displaying of absolute surcharges of template options and its values with non-default currencies
- PT-11587 - Dokumentenerstellung verbessert
- PT-11701 - Fixes duplication of templates

# 1.3.1
- PT-11652 - Fixes an error where the upload endpoint is incorrectly
- PT-11651 - Fixes a bug where certain file names could not be uploaded
- PT-11164 - Fixes bug which prevented uploading the same file twice in a row

# 1.3.0
- PT-11607 - Shopware 6.2 compatibility
- PT-11474 - Implemented duplication of custom products
- PT-11426 - Implemented exclusion handling
- PT-10937 - Implemented file and image upload

# 1.2.1
- NTR - Fix account order overview

# 1.2.0
- PT-10906 - Adds step-by-step mode
- PT-11306 - Adds option values count column which outputs the amount of assigned option values
- PT-11309 - Changes naming of "order number" to "option product number" in options
- PT-11314 - Improves compatibility with QuickView of CMS extension plugin
- PT-11316 - Adds assignment information box
- PT-11355 - Line breaks are now visible in the cart
- PT-11422 - Adds HTML editor option
- PT-11441 - Fixes the german translation of the description field of an option
- PT-11454 - Fixes indentation of selection options
- PT-11482 - Removes placeholder field from the number field option
- PT-11496 - Prices will only be displayed next to the value when the option is a selection
- PT-11554 - Fixes HTML editor configuration possibilities

# 1.1.0
- PT-10720 - Improves error handling in option modal
- PT-11110 - Improves extension of documents
- PT-11226 - Fixes link to product detail page from order module
- PT-11227 - Adds surcharge information to orders of Custom Products and fixes the price in its parent position listing
- PT-11236 - Solves an issue with validation of colorpicker
- PT-11249 - Solves an issue where price rules couldn't be added
- PT-11250 - Solves an issue with invalid selection options
- PT-11253 - Replaced checkboxes with toggle switches
- PT-11255 - Fixes required field of selection options in storefront
- PT-11278 - Applys style fixes for Storefront surcharges
- PT-11279 - Extension of the order module optimized
- PT-11280 - Adds the new cart layout to order history
- PT-11282 - Hide list prices in option modal
- PT-11286 - Fixes editing of the name of tree items. Adds a button to add a subelement
- PT-11289 - Description for option types is now displayed in the storefront
- PT-11290 - Solves an issue with display of surcharge in cart
- PT-11302 - Adds imageselect to option types
- PT-11307 - Removed the unnecessary required option from the checkbox option type
- PT-11311 - Add expand and shrink function to text options in cart
- PT-11315 - Optimizes placeholders
- PT-11352 - Implements imageselect renderer for the order overview
- PT-11362 - Option surcharges are now calculated via actual price of quantity 1

# 1.0.0
- PT-11144 - Solves an issue where Custom Products wouldn't work with product variants
- PT-11145 - Solves an issue where required options wouldn't get validated
- PT-11149 - Enables reordering of Custom Products in the storefront account
- PT-11150 - Introduce error handling to the administration
- PT-11151 - Solves an issue where the empty state of the option listing disappears
- PT-11154 - Solves an issue where same configured products wouldn't get grouped
- PT-11162 - Adds the new cart layout
- PT-11180 - Solves an issue where Custom Products could be bought without configuring them
- PT-11198 - Solves an issue with order document creation
- PT-11218 - Enhances managing the translations of options
- PT-11219, PT-11208, PT-11159 - Storefront style optimizations
- PT-11220 - Solves an issue with the order confirmation mail
- PT-11236 - Optimizes option handling in the storefront

# 0.9.0
- Initial Custom Products release for Shopware 6
