<?php

// autoload_classmap.php @generated by Composer

$vendorDir = dirname(dirname(__FILE__));
$baseDir = dirname($vendorDir);

return array(
    'KlarnaPayment\\Command\\ActivatePaymentMethods' => $baseDir . '/src/Command/ActivatePaymentMethods.php',
    'KlarnaPayment\\Components\\ButtonKeyHandler\\ButtonKeyHandler' => $baseDir . '/src/Components/ButtonKeyHandler/ButtonKeyHandler.php',
    'KlarnaPayment\\Components\\ButtonKeyHandler\\ButtonKeyHandlerInterface' => $baseDir . '/src/Components/ButtonKeyHandler/ButtonKeyHandlerInterface.php',
    'KlarnaPayment\\Components\\CartHasher\\CartHasher' => $baseDir . '/src/Components/CartHasher/CartHasher.php',
    'KlarnaPayment\\Components\\CartHasher\\CartHasherInterface' => $baseDir . '/src/Components/CartHasher/CartHasherInterface.php',
    'KlarnaPayment\\Components\\CartHasher\\Exception\\InvalidCartHashException' => $baseDir . '/src/Components/CartHasher/Exception/InvalidCartHashException.php',
    'KlarnaPayment\\Components\\CartHasher\\InstantShoppingCartHasher' => $baseDir . '/src/Components/CartHasher/InstantShoppingCartHasher.php',
    'KlarnaPayment\\Components\\Client\\Client' => $baseDir . '/src/Components/Client/Client.php',
    'KlarnaPayment\\Components\\Client\\ClientInterface' => $baseDir . '/src/Components/Client/ClientInterface.php',
    'KlarnaPayment\\Components\\Client\\Hydrator\\Request\\CancelPayment\\CancelPaymentRequestHydrator' => $baseDir . '/src/Components/Client/Hydrator/Request/CancelPayment/CancelPaymentRequestHydrator.php',
    'KlarnaPayment\\Components\\Client\\Hydrator\\Request\\CancelPayment\\CancelPaymentRequestHydratorInterface' => $baseDir . '/src/Components/Client/Hydrator/Request/CancelPayment/CancelPaymentRequestHydratorInterface.php',
    'KlarnaPayment\\Components\\Client\\Hydrator\\Request\\CreateButtonKey\\CreateButtonKeyRequestHydrator' => $baseDir . '/src/Components/Client/Hydrator/Request/CreateButtonKey/CreateButtonKeyRequestHydrator.php',
    'KlarnaPayment\\Components\\Client\\Hydrator\\Request\\CreateButtonKey\\CreateButtonKeyRequestHydratorInterface' => $baseDir . '/src/Components/Client/Hydrator/Request/CreateButtonKey/CreateButtonKeyRequestHydratorInterface.php',
    'KlarnaPayment\\Components\\Client\\Hydrator\\Request\\CreateCapture\\CreateCaptureRequestHydrator' => $baseDir . '/src/Components/Client/Hydrator/Request/CreateCapture/CreateCaptureRequestHydrator.php',
    'KlarnaPayment\\Components\\Client\\Hydrator\\Request\\CreateCapture\\CreateCaptureRequestHydratorInterface' => $baseDir . '/src/Components/Client/Hydrator/Request/CreateCapture/CreateCaptureRequestHydratorInterface.php',
    'KlarnaPayment\\Components\\Client\\Hydrator\\Request\\CreateOrder\\CreateOrderRequestHydrator' => $baseDir . '/src/Components/Client/Hydrator/Request/CreateOrder/CreateOrderRequestHydrator.php',
    'KlarnaPayment\\Components\\Client\\Hydrator\\Request\\CreateOrder\\CreateOrderRequestHydratorInterface' => $baseDir . '/src/Components/Client/Hydrator/Request/CreateOrder/CreateOrderRequestHydratorInterface.php',
    'KlarnaPayment\\Components\\Client\\Hydrator\\Request\\CreateRefund\\CreateRefundRequestHydrator' => $baseDir . '/src/Components/Client/Hydrator/Request/CreateRefund/CreateRefundRequestHydrator.php',
    'KlarnaPayment\\Components\\Client\\Hydrator\\Request\\CreateRefund\\CreateRefundRequestHydratorInterface' => $baseDir . '/src/Components/Client/Hydrator/Request/CreateRefund/CreateRefundRequestHydratorInterface.php',
    'KlarnaPayment\\Components\\Client\\Hydrator\\Request\\CreateSession\\CreateSessionRequestHydrator' => $baseDir . '/src/Components/Client/Hydrator/Request/CreateSession/CreateSessionRequestHydrator.php',
    'KlarnaPayment\\Components\\Client\\Hydrator\\Request\\CreateSession\\CreateSessionRequestHydratorInterface' => $baseDir . '/src/Components/Client/Hydrator/Request/CreateSession/CreateSessionRequestHydratorInterface.php',
    'KlarnaPayment\\Components\\Client\\Hydrator\\Request\\ExtendAuthorization\\ExtendAuthorizationRequestHydrator' => $baseDir . '/src/Components/Client/Hydrator/Request/ExtendAuthorization/ExtendAuthorizationRequestHydrator.php',
    'KlarnaPayment\\Components\\Client\\Hydrator\\Request\\ExtendAuthorization\\ExtendAuthorizationRequestHydratorInterface' => $baseDir . '/src/Components/Client/Hydrator/Request/ExtendAuthorization/ExtendAuthorizationRequestHydratorInterface.php',
    'KlarnaPayment\\Components\\Client\\Hydrator\\Request\\GetOrder\\GetOrderRequestHydrator' => $baseDir . '/src/Components/Client/Hydrator/Request/GetOrder/GetOrderRequestHydrator.php',
    'KlarnaPayment\\Components\\Client\\Hydrator\\Request\\GetOrder\\GetOrderRequestHydratorInterface' => $baseDir . '/src/Components/Client/Hydrator/Request/GetOrder/GetOrderRequestHydratorInterface.php',
    'KlarnaPayment\\Components\\Client\\Hydrator\\Request\\ReleaseRemainingAuthorization\\ReleaseRemainingAuthorizationHydrator' => $baseDir . '/src/Components/Client/Hydrator/Request/ReleaseRemainingAuthorization/ReleaseRemainingAuthorizationHydrator.php',
    'KlarnaPayment\\Components\\Client\\Hydrator\\Request\\ReleaseRemainingAuthorization\\ReleaseRemainingAuthorizationHydratorInterface' => $baseDir . '/src/Components/Client/Hydrator/Request/ReleaseRemainingAuthorization/ReleaseRemainingAuthorizationHydratorInterface.php',
    'KlarnaPayment\\Components\\Client\\Hydrator\\Request\\Test\\TestRequestHydrator' => $baseDir . '/src/Components/Client/Hydrator/Request/Test/TestRequestHydrator.php',
    'KlarnaPayment\\Components\\Client\\Hydrator\\Request\\Test\\TestRequestHydratorInterface' => $baseDir . '/src/Components/Client/Hydrator/Request/Test/TestRequestHydratorInterface.php',
    'KlarnaPayment\\Components\\Client\\Hydrator\\Request\\UpdateAddress\\UpdateAddressRequestHydrator' => $baseDir . '/src/Components/Client/Hydrator/Request/UpdateAddress/UpdateAddressRequestHydrator.php',
    'KlarnaPayment\\Components\\Client\\Hydrator\\Request\\UpdateAddress\\UpdateAddressRequestHydratorInterface' => $baseDir . '/src/Components/Client/Hydrator/Request/UpdateAddress/UpdateAddressRequestHydratorInterface.php',
    'KlarnaPayment\\Components\\Client\\Hydrator\\Request\\UpdateOrder\\UpdateOrderRequestHydrator' => $baseDir . '/src/Components/Client/Hydrator/Request/UpdateOrder/UpdateOrderRequestHydrator.php',
    'KlarnaPayment\\Components\\Client\\Hydrator\\Request\\UpdateOrder\\UpdateOrderRequestHydratorInterface' => $baseDir . '/src/Components/Client/Hydrator/Request/UpdateOrder/UpdateOrderRequestHydratorInterface.php',
    'KlarnaPayment\\Components\\Client\\Hydrator\\Response\\GetOrder\\GetOrderResponseHydrator' => $baseDir . '/src/Components/Client/Hydrator/Response/GetOrder/GetOrderResponseHydrator.php',
    'KlarnaPayment\\Components\\Client\\Hydrator\\Response\\GetOrder\\GetOrderResponseHydratorInterface' => $baseDir . '/src/Components/Client/Hydrator/Response/GetOrder/GetOrderResponseHydratorInterface.php',
    'KlarnaPayment\\Components\\Client\\Hydrator\\Struct\\Address\\AddressStructHydrator' => $baseDir . '/src/Components/Client/Hydrator/Struct/Address/AddressStructHydrator.php',
    'KlarnaPayment\\Components\\Client\\Hydrator\\Struct\\Address\\AddressStructHydratorInterface' => $baseDir . '/src/Components/Client/Hydrator/Struct/Address/AddressStructHydratorInterface.php',
    'KlarnaPayment\\Components\\Client\\Hydrator\\Struct\\Customer\\CustomerStructHydrator' => $baseDir . '/src/Components/Client/Hydrator/Struct/Customer/CustomerStructHydrator.php',
    'KlarnaPayment\\Components\\Client\\Hydrator\\Struct\\Customer\\CustomerStructHydratorInterface' => $baseDir . '/src/Components/Client/Hydrator/Struct/Customer/CustomerStructHydratorInterface.php',
    'KlarnaPayment\\Components\\Client\\Hydrator\\Struct\\Delivery\\DeliveryStructHydrator' => $baseDir . '/src/Components/Client/Hydrator/Struct/Delivery/DeliveryStructHydrator.php',
    'KlarnaPayment\\Components\\Client\\Hydrator\\Struct\\Delivery\\DeliveryStructHydratorInterface' => $baseDir . '/src/Components/Client/Hydrator/Struct/Delivery/DeliveryStructHydratorInterface.php',
    'KlarnaPayment\\Components\\Client\\Hydrator\\Struct\\LineItem\\LineItemStructHydrator' => $baseDir . '/src/Components/Client/Hydrator/Struct/LineItem/LineItemStructHydrator.php',
    'KlarnaPayment\\Components\\Client\\Hydrator\\Struct\\LineItem\\LineItemStructHydratorInterface' => $baseDir . '/src/Components/Client/Hydrator/Struct/LineItem/LineItemStructHydratorInterface.php',
    'KlarnaPayment\\Components\\Client\\Hydrator\\Struct\\ProductIdentifier\\ProductIdentifierStructHydrator' => $baseDir . '/src/Components/Client/Hydrator/Struct/ProductIdentifier/ProductIdentifierStructHydrator.php',
    'KlarnaPayment\\Components\\Client\\Hydrator\\Struct\\ProductIdentifier\\ProductIdentifierStructHydratorInterface' => $baseDir . '/src/Components/Client/Hydrator/Struct/ProductIdentifier/ProductIdentifierStructHydratorInterface.php',
    'KlarnaPayment\\Components\\Client\\Hydrator\\Struct\\ShippingOptions\\ShippingOptionsStructHydrator' => $baseDir . '/src/Components/Client/Hydrator/Struct/ShippingOptions/ShippingOptionsStructHydrator.php',
    'KlarnaPayment\\Components\\Client\\Hydrator\\Struct\\ShippingOptions\\ShippingOptionsStructHydratorInterface' => $baseDir . '/src/Components/Client/Hydrator/Struct/ShippingOptions/ShippingOptionsStructHydratorInterface.php',
    'KlarnaPayment\\Components\\Client\\Request\\CancelPaymentRequest' => $baseDir . '/src/Components/Client/Request/CancelPaymentRequest.php',
    'KlarnaPayment\\Components\\Client\\Request\\CreateButtonKeyRequest' => $baseDir . '/src/Components/Client/Request/CreateButtonKeyRequest.php',
    'KlarnaPayment\\Components\\Client\\Request\\CreateCaptureRequest' => $baseDir . '/src/Components/Client/Request/CreateCaptureRequest.php',
    'KlarnaPayment\\Components\\Client\\Request\\CreateOrderRequest' => $baseDir . '/src/Components/Client/Request/CreateOrderRequest.php',
    'KlarnaPayment\\Components\\Client\\Request\\CreateRefundRequest' => $baseDir . '/src/Components/Client/Request/CreateRefundRequest.php',
    'KlarnaPayment\\Components\\Client\\Request\\CreateSessionRequest' => $baseDir . '/src/Components/Client/Request/CreateSessionRequest.php',
    'KlarnaPayment\\Components\\Client\\Request\\DenyOrderRequest' => $baseDir . '/src/Components/Client/Request/DenyOrderRequest.php',
    'KlarnaPayment\\Components\\Client\\Request\\ExtendAuthorizationRequest' => $baseDir . '/src/Components/Client/Request/ExtendAuthorizationRequest.php',
    'KlarnaPayment\\Components\\Client\\Request\\GetOrderRequest' => $baseDir . '/src/Components/Client/Request/GetOrderRequest.php',
    'KlarnaPayment\\Components\\Client\\Request\\ReleaseRemainingAuthorizationRequest' => $baseDir . '/src/Components/Client/Request/ReleaseRemainingAuthorizationRequest.php',
    'KlarnaPayment\\Components\\Client\\Request\\RequestInterface' => $baseDir . '/src/Components/Client/Request/RequestInterface.php',
    'KlarnaPayment\\Components\\Client\\Request\\TestRequest' => $baseDir . '/src/Components/Client/Request/TestRequest.php',
    'KlarnaPayment\\Components\\Client\\Request\\UpdateAddressRequest' => $baseDir . '/src/Components/Client/Request/UpdateAddressRequest.php',
    'KlarnaPayment\\Components\\Client\\Request\\UpdateOrderRequest' => $baseDir . '/src/Components/Client/Request/UpdateOrderRequest.php',
    'KlarnaPayment\\Components\\Client\\Response\\GenericResponse' => $baseDir . '/src/Components/Client/Response/GenericResponse.php',
    'KlarnaPayment\\Components\\Client\\Response\\GetOrderResponse' => $baseDir . '/src/Components/Client/Response/GetOrderResponse.php',
    'KlarnaPayment\\Components\\Client\\Response\\ResponseInterface' => $baseDir . '/src/Components/Client/Response/ResponseInterface.php',
    'KlarnaPayment\\Components\\Client\\Struct\\Address' => $baseDir . '/src/Components/Client/Struct/Address.php',
    'KlarnaPayment\\Components\\Client\\Struct\\Attachment' => $baseDir . '/src/Components/Client/Struct/Attachment.php',
    'KlarnaPayment\\Components\\Client\\Struct\\Customer' => $baseDir . '/src/Components/Client/Struct/Customer.php',
    'KlarnaPayment\\Components\\Client\\Struct\\LineItem' => $baseDir . '/src/Components/Client/Struct/LineItem.php',
    'KlarnaPayment\\Components\\Client\\Struct\\Options' => $baseDir . '/src/Components/Client/Struct/Options.php',
    'KlarnaPayment\\Components\\Client\\Struct\\ProductIdentifier' => $baseDir . '/src/Components/Client/Struct/ProductIdentifier.php',
    'KlarnaPayment\\Components\\Client\\Struct\\ShippingOption' => $baseDir . '/src/Components/Client/Struct/ShippingOption.php',
    'KlarnaPayment\\Components\\Client\\Struct\\Styling' => $baseDir . '/src/Components/Client/Struct/Styling.php',
    'KlarnaPayment\\Components\\ConfigReader\\ConfigReader' => $baseDir . '/src/Components/ConfigReader/ConfigReader.php',
    'KlarnaPayment\\Components\\ConfigReader\\ConfigReaderInterface' => $baseDir . '/src/Components/ConfigReader/ConfigReaderInterface.php',
    'KlarnaPayment\\Components\\Controller\\Administration\\OrderController' => $baseDir . '/src/Components/Controller/Administration/OrderController.php',
    'KlarnaPayment\\Components\\Controller\\Administration\\OrderUpdateController' => $baseDir . '/src/Components/Controller/Administration/OrderUpdateController.php',
    'KlarnaPayment\\Components\\Controller\\Administration\\SettingsController' => $baseDir . '/src/Components/Controller/Administration/SettingsController.php',
    'KlarnaPayment\\Components\\Controller\\Administration\\WizardController' => $baseDir . '/src/Components/Controller/Administration/WizardController.php',
    'KlarnaPayment\\Components\\Controller\\Storefront\\CallbackController' => $baseDir . '/src/Components/Controller/Storefront/CallbackController.php',
    'KlarnaPayment\\Components\\Controller\\Storefront\\InstantShoppingController' => $baseDir . '/src/Components/Controller/Storefront/InstantShoppingController.php',
    'KlarnaPayment\\Components\\CookieProvider\\CookieProvider' => $baseDir . '/src/Components/CookieProvider/CookieProvider.php',
    'KlarnaPayment\\Components\\DataAbstractionLayer\\Entity\\ButtonKey\\ButtonKeyCollection' => $baseDir . '/src/Components/DataAbstractionLayer/Entity/ButtonKey/ButtonKeyCollection.php',
    'KlarnaPayment\\Components\\DataAbstractionLayer\\Entity\\ButtonKey\\ButtonKeyDefinition' => $baseDir . '/src/Components/DataAbstractionLayer/Entity/ButtonKey/ButtonKeyDefinition.php',
    'KlarnaPayment\\Components\\DataAbstractionLayer\\Entity\\ButtonKey\\ButtonKeyEntity' => $baseDir . '/src/Components/DataAbstractionLayer/Entity/ButtonKey/ButtonKeyEntity.php',
    'KlarnaPayment\\Components\\DataAbstractionLayer\\Entity\\RequestLog\\RequestLogCollection' => $baseDir . '/src/Components/DataAbstractionLayer/Entity/RequestLog/RequestLogCollection.php',
    'KlarnaPayment\\Components\\DataAbstractionLayer\\Entity\\RequestLog\\RequestLogDefinition' => $baseDir . '/src/Components/DataAbstractionLayer/Entity/RequestLog/RequestLogDefinition.php',
    'KlarnaPayment\\Components\\DataAbstractionLayer\\Entity\\RequestLog\\RequestLogEntity' => $baseDir . '/src/Components/DataAbstractionLayer/Entity/RequestLog/RequestLogEntity.php',
    'KlarnaPayment\\Components\\EventListener\\CheckoutEventListener' => $baseDir . '/src/Components/EventListener/CheckoutEventListener.php',
    'KlarnaPayment\\Components\\EventListener\\FooterBadgeEventListener' => $baseDir . '/src/Components/EventListener/FooterBadgeEventListener.php',
    'KlarnaPayment\\Components\\EventListener\\OrderChangeEventListener' => $baseDir . '/src/Components/EventListener/OrderChangeEventListener.php',
    'KlarnaPayment\\Components\\EventListener\\OrderStatusTransitionEventListener' => $baseDir . '/src/Components/EventListener/OrderStatusTransitionEventListener.php',
    'KlarnaPayment\\Components\\EventListener\\OrderValidationEventListener' => $baseDir . '/src/Components/EventListener/OrderValidationEventListener.php',
    'KlarnaPayment\\Components\\EventListener\\PaymentMethodEventListener' => $baseDir . '/src/Components/EventListener/PaymentMethodEventListener.php',
    'KlarnaPayment\\Components\\EventListener\\SalesChannelChangeEventListener' => $baseDir . '/src/Components/EventListener/SalesChannelChangeEventListener.php',
    'KlarnaPayment\\Components\\EventListener\\SessionEventListener' => $baseDir . '/src/Components/EventListener/SessionEventListener.php',
    'KlarnaPayment\\Components\\EventListener\\TemplateDataEventListener' => $baseDir . '/src/Components/EventListener/TemplateDataEventListener.php',
    'KlarnaPayment\\Components\\Event\\SetExtraMerchantDataEvent' => $baseDir . '/src/Components/Event/SetExtraMerchantDataEvent.php',
    'KlarnaPayment\\Components\\Exception\\ButtonKeyCreationFailed' => $baseDir . '/src/Components/Exception/ButtonKeyCreationFailed.php',
    'KlarnaPayment\\Components\\Exception\\InvalidMerchantData' => $baseDir . '/src/Components/Exception/InvalidMerchantData.php',
    'KlarnaPayment\\Components\\Extension\\ErrorMessageExtension' => $baseDir . '/src/Components/Extension/ErrorMessageExtension.php',
    'KlarnaPayment\\Components\\Extension\\GuestCustomerRegistrationExtension' => $baseDir . '/src/Components/Extension/GuestCustomerRegistrationExtension.php',
    'KlarnaPayment\\Components\\Extension\\Hydrator\\InstantShopping\\DataExtensionHydrator' => $baseDir . '/src/Components/Extension/Hydrator/InstantShopping/DataExtensionHydrator.php',
    'KlarnaPayment\\Components\\Extension\\Hydrator\\InstantShopping\\DataExtensionHydratorInterface' => $baseDir . '/src/Components/Extension/Hydrator/InstantShopping/DataExtensionHydratorInterface.php',
    'KlarnaPayment\\Components\\Extension\\SessionDataExtension' => $baseDir . '/src/Components/Extension/SessionDataExtension.php',
    'KlarnaPayment\\Components\\Extension\\TemplateData\\CheckoutDataExtension' => $baseDir . '/src/Components/Extension/TemplateData/CheckoutDataExtension.php',
    'KlarnaPayment\\Components\\Extension\\TemplateData\\InstantShoppingDataExtension' => $baseDir . '/src/Components/Extension/TemplateData/InstantShoppingDataExtension.php',
    'KlarnaPayment\\Components\\Extension\\TemplateData\\OnsiteMessagingDataExtension' => $baseDir . '/src/Components/Extension/TemplateData/OnsiteMessagingDataExtension.php',
    'KlarnaPayment\\Components\\Factory\\MerchantDataFactory' => $baseDir . '/src/Components/Factory/MerchantDataFactory.php',
    'KlarnaPayment\\Components\\Factory\\MerchantDataFactoryInterface' => $baseDir . '/src/Components/Factory/MerchantDataFactoryInterface.php',
    'KlarnaPayment\\Components\\Helper\\CurrencyHelper\\CurrencyHelper' => $baseDir . '/src/Components/Helper/CurrencyHelper/CurrencyHelper.php',
    'KlarnaPayment\\Components\\Helper\\CurrencyHelper\\CurrencyHelperInterface' => $baseDir . '/src/Components/Helper/CurrencyHelper/CurrencyHelperInterface.php',
    'KlarnaPayment\\Components\\Helper\\LocaleHelper\\LocaleHelper' => $baseDir . '/src/Components/Helper/LocaleHelper/LocaleHelper.php',
    'KlarnaPayment\\Components\\Helper\\LocaleHelper\\LocaleHelperInterface' => $baseDir . '/src/Components/Helper/LocaleHelper/LocaleHelperInterface.php',
    'KlarnaPayment\\Components\\Helper\\MerchantUrlHelper\\MerchantUrlHelper' => $baseDir . '/src/Components/Helper/MerchantUrlHelper/MerchantUrlHelper.php',
    'KlarnaPayment\\Components\\Helper\\MerchantUrlHelper\\MerchantUrlHelperInterface' => $baseDir . '/src/Components/Helper/MerchantUrlHelper/MerchantUrlHelperInterface.php',
    'KlarnaPayment\\Components\\Helper\\OrderFetcher' => $baseDir . '/src/Components/Helper/OrderFetcher.php',
    'KlarnaPayment\\Components\\Helper\\OrderFetcherInterface' => $baseDir . '/src/Components/Helper/OrderFetcherInterface.php',
    'KlarnaPayment\\Components\\Helper\\OrderValidator\\OrderValidator' => $baseDir . '/src/Components/Helper/OrderValidator/OrderValidator.php',
    'KlarnaPayment\\Components\\Helper\\OrderValidator\\OrderValidatorInterface' => $baseDir . '/src/Components/Helper/OrderValidator/OrderValidatorInterface.php',
    'KlarnaPayment\\Components\\Helper\\PaymentHelper\\PaymentHelper' => $baseDir . '/src/Components/Helper/PaymentHelper/PaymentHelper.php',
    'KlarnaPayment\\Components\\Helper\\PaymentHelper\\PaymentHelperInterface' => $baseDir . '/src/Components/Helper/PaymentHelper/PaymentHelperInterface.php',
    'KlarnaPayment\\Components\\Helper\\RequestHasher' => $baseDir . '/src/Components/Helper/RequestHasher.php',
    'KlarnaPayment\\Components\\Helper\\RequestHasherInterface' => $baseDir . '/src/Components/Helper/RequestHasherInterface.php',
    'KlarnaPayment\\Components\\Helper\\SalesChannelHelper\\SalesChannelHelper' => $baseDir . '/src/Components/Helper/SalesChannelHelper/SalesChannelHelper.php',
    'KlarnaPayment\\Components\\Helper\\SalesChannelHelper\\SalesChannelHelperInterface' => $baseDir . '/src/Components/Helper/SalesChannelHelper/SalesChannelHelperInterface.php',
    'KlarnaPayment\\Components\\Helper\\SeoUrlHelper\\SeoUrlHelper' => $baseDir . '/src/Components/Helper/SeoUrlHelper/SeoUrlHelper.php',
    'KlarnaPayment\\Components\\Helper\\SeoUrlHelper\\SeoUrlHelperInterface' => $baseDir . '/src/Components/Helper/SeoUrlHelper/SeoUrlHelperInterface.php',
    'KlarnaPayment\\Components\\Helper\\ShippingMethodHelper\\ShippingMethodHelper' => $baseDir . '/src/Components/Helper/ShippingMethodHelper/ShippingMethodHelper.php',
    'KlarnaPayment\\Components\\Helper\\ShippingMethodHelper\\ShippingMethodHelperInterface' => $baseDir . '/src/Components/Helper/ShippingMethodHelper/ShippingMethodHelperInterface.php',
    'KlarnaPayment\\Components\\Helper\\StateHelper\\Cancel\\CancelStateHelper' => $baseDir . '/src/Components/Helper/StateHelper/Cancel/CancelStateHelper.php',
    'KlarnaPayment\\Components\\Helper\\StateHelper\\Cancel\\CancelStateHelperInterface' => $baseDir . '/src/Components/Helper/StateHelper/Cancel/CancelStateHelperInterface.php',
    'KlarnaPayment\\Components\\Helper\\StateHelper\\Capture\\CaptureStateHelper' => $baseDir . '/src/Components/Helper/StateHelper/Capture/CaptureStateHelper.php',
    'KlarnaPayment\\Components\\Helper\\StateHelper\\Capture\\CaptureStateHelperInterface' => $baseDir . '/src/Components/Helper/StateHelper/Capture/CaptureStateHelperInterface.php',
    'KlarnaPayment\\Components\\Helper\\StateHelper\\Refund\\RefundStateHelper' => $baseDir . '/src/Components/Helper/StateHelper/Refund/RefundStateHelper.php',
    'KlarnaPayment\\Components\\Helper\\StateHelper\\Refund\\RefundStateHelperInterface' => $baseDir . '/src/Components/Helper/StateHelper/Refund/RefundStateHelperInterface.php',
    'KlarnaPayment\\Components\\Helper\\StateHelper\\StateData\\StateDataHelper' => $baseDir . '/src/Components/Helper/StateHelper/StateData/StateDataHelper.php',
    'KlarnaPayment\\Components\\Helper\\StateHelper\\StateData\\StateDataHelperInterface' => $baseDir . '/src/Components/Helper/StateHelper/StateData/StateDataHelperInterface.php',
    'KlarnaPayment\\Components\\InstantShopping\\CartHandler\\CartHandler' => $baseDir . '/src/Components/InstantShopping/CartHandler/CartHandler.php',
    'KlarnaPayment\\Components\\InstantShopping\\CartHandler\\CartHandlerInterface' => $baseDir . '/src/Components/InstantShopping/CartHandler/CartHandlerInterface.php',
    'KlarnaPayment\\Components\\InstantShopping\\ContextHandler\\ContextHandler' => $baseDir . '/src/Components/InstantShopping/ContextHandler/ContextHandler.php',
    'KlarnaPayment\\Components\\InstantShopping\\ContextHandler\\ContextHandlerInterface' => $baseDir . '/src/Components/InstantShopping/ContextHandler/ContextHandlerInterface.php',
    'KlarnaPayment\\Components\\InstantShopping\\CustomerHandler\\CustomerHandler' => $baseDir . '/src/Components/InstantShopping/CustomerHandler/CustomerHandler.php',
    'KlarnaPayment\\Components\\InstantShopping\\CustomerHandler\\CustomerHandlerInterface' => $baseDir . '/src/Components/InstantShopping/CustomerHandler/CustomerHandlerInterface.php',
    'KlarnaPayment\\Components\\InstantShopping\\DataProvider\\LoadDataProvider' => $baseDir . '/src/Components/InstantShopping/DataProvider/LoadDataProvider.php',
    'KlarnaPayment\\Components\\InstantShopping\\DataProvider\\LoadDataProviderInterface' => $baseDir . '/src/Components/InstantShopping/DataProvider/LoadDataProviderInterface.php',
    'KlarnaPayment\\Components\\InstantShopping\\DataProvider\\PlaceOrderCallbackProvider' => $baseDir . '/src/Components/InstantShopping/DataProvider/PlaceOrderCallbackProvider.php',
    'KlarnaPayment\\Components\\InstantShopping\\DataProvider\\PlaceOrderCallbackProviderInterface' => $baseDir . '/src/Components/InstantShopping/DataProvider/PlaceOrderCallbackProviderInterface.php',
    'KlarnaPayment\\Components\\InstantShopping\\DataProvider\\UpdateCallbackProvider' => $baseDir . '/src/Components/InstantShopping/DataProvider/UpdateCallbackProvider.php',
    'KlarnaPayment\\Components\\InstantShopping\\DataProvider\\UpdateCallbackProviderInterface' => $baseDir . '/src/Components/InstantShopping/DataProvider/UpdateCallbackProviderInterface.php',
    'KlarnaPayment\\Components\\InstantShopping\\DataProvider\\UpdateDataProvider' => $baseDir . '/src/Components/InstantShopping/DataProvider/UpdateDataProvider.php',
    'KlarnaPayment\\Components\\InstantShopping\\DataProvider\\UpdateDataProviderInterface' => $baseDir . '/src/Components/InstantShopping/DataProvider/UpdateDataProviderInterface.php',
    'KlarnaPayment\\Components\\InstantShopping\\MerchantDataProvider\\MerchantDataProvider' => $baseDir . '/src/Components/InstantShopping/MerchantDataProvider/MerchantDataProvider.php',
    'KlarnaPayment\\Components\\InstantShopping\\MerchantDataProvider\\MerchantDataProviderInterface' => $baseDir . '/src/Components/InstantShopping/MerchantDataProvider/MerchantDataProviderInterface.php',
    'KlarnaPayment\\Components\\InstantShopping\\OrderHandler\\OrderHandler' => $baseDir . '/src/Components/InstantShopping/OrderHandler/OrderHandler.php',
    'KlarnaPayment\\Components\\InstantShopping\\OrderHandler\\OrderHandlerInterface' => $baseDir . '/src/Components/InstantShopping/OrderHandler/OrderHandlerInterface.php',
    'KlarnaPayment\\Components\\OnsiteMessagingReplacer\\PlaceholderReplacerInterface' => $baseDir . '/src/Components/OnsiteMessagingReplacer/PlaceholderReplacerInterface.php',
    'KlarnaPayment\\Components\\OnsiteMessagingReplacer\\ProductPriceReplacer' => $baseDir . '/src/Components/OnsiteMessagingReplacer/ProductPriceReplacer.php',
    'KlarnaPayment\\Components\\PaymentHandler\\AbstractKlarnaPaymentHandler' => $baseDir . '/src/Components/PaymentHandler/AbstractKlarnaPaymentHandler.php',
    'KlarnaPayment\\Components\\PaymentHandler\\KlarnaInstantShoppingPaymentHandler' => $baseDir . '/src/Components/PaymentHandler/KlarnaInstantShoppingPaymentHandler.php',
    'KlarnaPayment\\Components\\PaymentHandler\\KlarnaPaymentsPaymentHandler' => $baseDir . '/src/Components/PaymentHandler/KlarnaPaymentsPaymentHandler.php',
    'KlarnaPayment\\Components\\Struct\\Configuration' => $baseDir . '/src/Components/Struct/Configuration.php',
    'KlarnaPayment\\Components\\Struct\\ExtraMerchantData' => $baseDir . '/src/Components/Struct/ExtraMerchantData.php',
    'KlarnaPayment\\Components\\Struct\\PaymentMethodBadge' => $baseDir . '/src/Components/Struct/PaymentMethodBadge.php',
    'KlarnaPayment\\Components\\Validator\\CartHash' => $baseDir . '/src/Components/Validator/CartHash.php',
    'KlarnaPayment\\Components\\Validator\\CartHashValidator' => $baseDir . '/src/Components/Validator/CartHashValidator.php',
    'KlarnaPayment\\Components\\Validator\\OnsiteMessagingValidator' => $baseDir . '/src/Components/Validator/OnsiteMessagingValidator.php',
    'KlarnaPayment\\Components\\Validator\\OrderTransitionChangeValidator' => $baseDir . '/src/Components/Validator/OrderTransitionChangeValidator.php',
    'KlarnaPayment\\Components\\Validator\\PaymentMethodValidator' => $baseDir . '/src/Components/Validator/PaymentMethodValidator.php',
    'KlarnaPayment\\Core\\Framework\\ContextScope' => $baseDir . '/src/Core/Framework/ContextScope.php',
    'KlarnaPayment\\Core\\System\\NumberRange\\ValueGenerator\\NumberRangeValueGenerator' => $baseDir . '/src/Core/System/NumberRange/ValueGenerator/NumberRangeValueGenerator.php',
    'KlarnaPayment\\Core\\System\\SystemConfig\\SystemConfigService' => $baseDir . '/src/Core/System/SystemConfig/SystemConfigService.php',
    'KlarnaPayment\\Exception\\OrderUpdateDeniedException' => $baseDir . '/src/Exception/OrderUpdateDeniedException.php',
    'KlarnaPayment\\Installer\\ConfigInstaller' => $baseDir . '/src/Installer/ConfigInstaller.php',
    'KlarnaPayment\\Installer\\CustomFieldInstaller' => $baseDir . '/src/Installer/CustomFieldInstaller.php',
    'KlarnaPayment\\Installer\\InstallerInterface' => $baseDir . '/src/Installer/InstallerInterface.php',
    'KlarnaPayment\\Installer\\PaymentMethodInstaller' => $baseDir . '/src/Installer/PaymentMethodInstaller.php',
    'KlarnaPayment\\Installer\\RuleInstaller' => $baseDir . '/src/Installer/RuleInstaller.php',
    'KlarnaPayment\\KlarnaPayment' => $baseDir . '/src/KlarnaPayment.php',
    'KlarnaPayment\\Migration\\Migration1570840015AddLogTable' => $baseDir . '/src/Migration/Migration1570840015AddLogTable.php',
    'KlarnaPayment\\Migration\\Migration1580400794RenameLogTable' => $baseDir . '/src/Migration/Migration1580400794RenameLogTable.php',
    'KlarnaPayment\\Migration\\Migration1580401225AddButtonKeyTable' => $baseDir . '/src/Migration/Migration1580401225AddButtonKeyTable.php',
    'KlarnaPayment\\Resources\\translations\\de_DE\\SnippetFile_de_DE' => $baseDir . '/src/Resources/translations/de_DE/SnippetFile_de_DE.php',
    'KlarnaPayment\\Resources\\translations\\en_GB\\SnippetFile_en_GB' => $baseDir . '/src/Resources/translations/en_GB/SnippetFile_en_GB.php',
);
