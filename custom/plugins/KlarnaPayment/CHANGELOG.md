# 1.3.7
- Fix plugin configuration value errors
- Please note: There are known erroneous configuration behaviours with sales channel inheritance in the documentation

# 1.3.6
- Improve error handling for Instant Shopping
- Fix missing labels in plugin configuration
- Added note in documentation (https://klarna.pluginwerk.de/en/1-3/index.html#h-translation-of-the-payment-methods) about translation of payment methods.

# 1.3.5
- Add default name for payment methods for the installation with a different system language

# 1.3.4
- Fix handling of promotions in cart

# 1.3.3
- Fix automatic package of zip archive to include compiled files again
- Fix use of wrong exception class

# 1.3.2
- Fix styling issues of the Instant Shopping button
- Fix error on updating order when order has been captured
- Improve performance in checkout and order update

# 1.3.1
- Fix error when selecting an order from the search results

# 1.3.0
- Add locale and currency to the rule for available Klarna payment methods in the checkout
- Send shipping tracking information to the Klarna merchant portal  
- Fix error during installation if a language could not be found
- Fix Instant Shopping for guests with activated required registration settings
- Optimize validation of API credentials

# 1.2.1
- Fix delivery information display on detail pages
- Fix administration order updates for non-Klarna orders

# 1.2.0
- Added compatibility with Shopware 6.2

# 1.1.0
- Implementation of Klarna instant shopping
- Add support for net prices (starting from Shopware 6.2.0)

# 1.0.4
- Fix checkout confirmation button for non-Klarna payment methods

# 1.0.3
- Fix customer name for Klarna Payment session

# 1.0.2
- Added combined Pay Now payment method and Credit Card
- Order changes in the administration are verified with Klarna before persisting the changes
- Changed address structure
- Payment method categories can now be disabled from the plugin configuration
- Skip verification of order changes during order process before Klarna order is created

# 1.0.1
- Fixed a rounding issue for Klarna calls

# 1.0.0
- First version of the Klarna Payment integration for Shopware 6.1
