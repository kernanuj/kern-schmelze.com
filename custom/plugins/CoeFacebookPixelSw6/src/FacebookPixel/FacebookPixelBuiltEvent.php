<?php

namespace CoeFacebookPixelSw6\FacebookPixel;

use Shopware\Core\Framework\Context;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Contracts\EventDispatcher\Event;

class FacebookPixelBuiltEvent extends Event
{
    /** @var string  */
    public const EVENT_NAME = 'facebookpixel.built';

    /** @var string  */
    private $pixel;

    /** @var SalesChannelContext  */
    private $salesChannelContext;

    /** @var PixelBuilderServiceInterface  */
    private $pixelBuilder;

    /**
     * FacebookPixelBuiltEvent constructor.
     * @param PixelBuilderServiceInterface $pixelBuilder
     * @param string $pixel
     * @param SalesChannelContext $salesChannelContext
     */
    public function __construct(
        PixelBuilderServiceInterface $pixelBuilder,
        string $pixel,
        SalesChannelContext $salesChannelContext)
    {
        $this->pixelBuilder = $pixelBuilder;
        $this->pixel = $pixel;
        $this->salesChannelContext = $salesChannelContext;
    }

    /**
     * @return PixelBuilderServiceInterface
     */
    public function getPixelBuilder(): PixelBuilderServiceInterface
    {
        return $this->pixelBuilder;
    }

    /**
     * @return string
     */
    public function getPixel(): string
    {
        return $this->pixel;
    }

    /**
     * @return Context
     */
    public function getSalesChannelContext(): SalesChannelContext
    {
        return $this->salesChannelContext;
    }



}