<?php

namespace CoeFacebookPixelSw6\Subscriber;

use CoeFacebookPixelSw6\CoeUtil\CoeBaseUtil;
use CoeFacebookPixelSw6\CoeUtil\CoeSwUtil;
use CoeFacebookPixelSw6\CoeUtil\ControllerDataStruct;
use CoeFacebookPixelSw6\FacebookPixel\FacebookPixelBuiltEvent;
use CoeFacebookPixelSw6\FacebookPixel\FacebookPixelConfig;
use CoeFacebookPixelSw6\FacebookPixel\FacebookPixelHandlerFactory;
use CoeFacebookPixelSw6\FacebookPixel\PixelBuilder\GenericHandler;
use CoeFacebookPixelSw6\FacebookPixel\PixelBuilderFactory;
use Shopware\Core\Checkout\Cart\Event\LineItemAddedEvent;
use Shopware\Core\Checkout\Customer\Event\CustomerChangedPaymentMethodEvent;
use Shopware\Core\Checkout\Customer\Event\CustomerRegisterEvent;
use Shopware\Core\Content\Newsletter\NewsletterEvents;
use Shopware\Core\Framework\DataAbstractionLayer\DefinitionInstanceRegistry;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenEvent;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Event\StorefrontRenderEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Class PageLoadSubscriber
 * @package CoeFacebookPixelSw6\Subscriber
 * @author Jeffry Block <jeffry.block@codeenterprise.de>
 */
class PageLoadSubscriber implements EventSubscriberInterface
{
    /** @var FacebookPixelConfig */
    private $config;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    /** @var DefinitionInstanceRegistry */
    private $definitionRegistry;

    /** @var Session */
    private $session;

    /**
     * PageLoadSubscriber constructor.
     * @param FacebookPixelConfig $config
     * @param EventDispatcherInterface $eventDispatcher
     * @param DefinitionInstanceRegistry $definitionRegistry
     * @param Session $session
     */
    public function __construct(
        FacebookPixelConfig $config,
        EventDispatcherInterface $eventDispatcher,
        DefinitionInstanceRegistry $definitionRegistry,
        Session $session
    ){
        $this->config = $config;
        $this->eventDispatcher = $eventDispatcher;
        $this->definitionRegistry = $definitionRegistry;
        $this->session = $session;
    }

    /**
     * @return array
     * @author Jeffry Block <jeffry.block@codeenterprise.de>
     */
    public static function getSubscribedEvents()
    {
        return [
            StorefrontRenderEvent::class => "onRenderStorefront",
            LineItemAddedEvent::class => "onLineItemAdded",
            CustomerRegisterEvent::class => "onCustomerRegister",
            CustomerChangedPaymentMethodEvent::class => "onPaymentMethodChange",
            NewsletterEvents::NEWSLETTER_RECIPIENT_WRITTEN_EVENT => "onNewsletterRegister" // use written event, register event doesn't work somehow...
        ];
    }

    /**
     * This is the main method, in which the data will get passed to the template
     * This method can not be called as an ajax request since we want our data to be displayed in the frontend
     * @param StorefrontRenderEvent $event
     * @author Jeffry Block <jeffry.block@codeenterprise.de>
     */
    public function onRenderStorefront(StorefrontRenderEvent $event)
    {
        $controller = $event->getRequest()->attributes->get("_controller");
        if(CoeBaseUtil::isAjaxRequest($event->getRequest()->server) || !$controller){
            return;
        }

        /** @var ControllerDataStruct $controllerData */
        $controllerData = CoeSwUtil::extractControllerNameAndAction($controller);

        if(!$this->config->getTrackingId()){
            return;
        }

        $cookies = $event->getRequest()->cookies;
        $this->config->setCookieAccepted(true);
        if(!$cookies->get("coeFacebookPixel") || $cookies->get("coeFacebookPixel") != "1"){
            $this->config->setCookieAccepted(false);
            $event->setParameter(FacebookPixelConfig::TWIG_VARIABLE, $this->config);
            return;
        }

        $this->transferSessionToConfig();

        /** @var SalesChannelContext $context */
        $salesChannelContext = $event->getSalesChannelContext();

        /** @var FacebookPixelHandlerInterface $controllerPixelBuilder */
        $controllerPixelBuilder = PixelBuilderFactory::create($controllerData);
        $controllerPixelBuilder->addPixel($event, $this->definitionRegistry, $this->config);

        $genericPixelBuilder = new GenericHandler($controllerPixelBuilder->getPixelBuilder());
        $genericPixelBuilder->addPixel($event,$this->definitionRegistry,$controllerPixelBuilder->getConfig());

        $this->config = $genericPixelBuilder->getConfig();

        //In order to manipulate the pixel data, use the setter methods of the $event->getPixelBuilder()
        //Use the addAdditionalTracking($prop,$value) method if no pre defined methods suits your needs
        //or decorate the PixelBuilderService
        $pixelBuiltEvent = $this->eventDispatcher->dispatch(new FacebookPixelBuiltEvent(
            $genericPixelBuilder->getPixelBuilder(),
            $genericPixelBuilder->getPixelBuilder()->getPixel(),
            $salesChannelContext
        ));

        $this->config->setPixel(
            $pixelBuiltEvent->getPixelBuilder()->getPixel()
        );

        $event->setParameter(FacebookPixelConfig::TWIG_VARIABLE, $this->config);
    }

    /**
     * @param LineItemAddedEvent $event
     * @author Jeffry Block <jeffry.block@codeenterprise.de>
     */
    public function onLineItemAdded(LineItemAddedEvent $event){
        $this->session->set("coeLastAddedProductId", $event->getLineItem()->getId());
    }

    /**
     * @param CustomerRegisterEvent $event
     * @author Jeffry Block <jeffry.block@codeenterprise.de>
     */
    public function onCustomerRegister(CustomerRegisterEvent $event){
        $this->session->set("coeIsNewRegistration", true);
    }

    /**
     * @param EntityWrittenEvent $event
     * @author Jeffry Block <jeffry.block@codeenterprise.de>
     */
    public function onNewsletterRegister(EntityWrittenEvent $event){
        $this->session->set("coeIsNewNewsletterSubscription", true);
    }

    /**
     * @param CustomerChangedPaymentMethodEvent $event
     * @author Jeffry Block <jeffry.block@codeenterprise.de>
     */
    public function onPaymentMethodChange(CustomerChangedPaymentMethodEvent $event){
        $newMethodId = $event->getRequestDataBag()->get("paymentMethodId");
        if($newMethodId){
            $this->session->set("coeNewPaymentMethodId", $newMethodId);
        }
    }

    /**
     * Adds some information to our session, accessible by the config
     * Needed for information which has been delivered by an event other then StorefrontRenderEvent
     * @author Jeffry Block <jeffry.block@codeenterprise.de>
     */
    private function transferSessionToConfig(){
        if($this->session->get("coeLastAddedProductId")){
            $this->config->setLastAddedProductId($this->session->get("coeLastAddedProductId"));
            $this->session->remove("coeLastAddedProductId");
        }
        if($this->session->get("coeIsNewRegistration")){
            $this->config->setIsNewRegistration($this->session->get("coeIsNewRegistration"));
            $this->session->remove("coeIsNewRegistration");
        }
        if($this->session->get("coeNewPaymentMethodId")){
            $this->config->setNewPaymentMethodId($this->session->get("coeNewPaymentMethodId"));
            $this->session->remove("coeNewPaymentMethodId");
        }
        if($this->session->get("coeIsNewNewsletterSubscription")){
            $this->config->setIsNewNewsletterSubscription($this->session->get("coeIsNewNewsletterSubscription"));
            $this->session->remove("coeIsNewNewsletterSubscription");
        }
    }
}