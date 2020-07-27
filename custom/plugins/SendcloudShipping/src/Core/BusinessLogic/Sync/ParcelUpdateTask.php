<?php

namespace Sendcloud\Shipping\Core\BusinessLogic\Sync;

use Sendcloud\Shipping\Core\Infrastructure\Logger\Logger;
use Sendcloud\Shipping\Core\Infrastructure\Utility\Exceptions\HttpAuthenticationException;
use Sendcloud\Shipping\Core\Infrastructure\Utility\Exceptions\HttpCommunicationException;
use Sendcloud\Shipping\Core\Infrastructure\Utility\Exceptions\HttpRequestException;

/**
 * Class ParcelUpdateTask
 * @package Sendcloud\Shipping\Core\BusinessLogic\Sync
 */
class ParcelUpdateTask extends BaseSyncTask
{
    const SENDCLOUD_STATUS_CANCELLED_ID = 2000;
    const SENDCLOUD_STATUS_CANCELLED = 'Cancelled';

    /**
     * @var int
     */
    private $timestamp;
    /**
     * @var string Order SendCloud UUID
     */
    private $shipmentUuid;
    /**
     * @var string Order ID
     */
    private $orderId;
    /**
     * @var string Order number
     */
    private $orderNumber;
    /**
     * @var string Order number
     */
    private $parcelId;

    /**
     * ParcelUpdateTask constructor.
     *
     * @param string $shipmentUuid
     * @param string $orderId
     * @param string $orderNumber
     * @param string $parcelId
     * @param int $timestamp
     */
    public function __construct($shipmentUuid, $orderId, $orderNumber, $parcelId, $timestamp)
    {
        $this->timestamp = $timestamp;
        $this->shipmentUuid = $shipmentUuid;
        $this->orderId = $orderId;
        $this->parcelId = $parcelId;
        $this->orderNumber = $orderNumber;
    }

    /**
     * String representation of object
     * @link https://php.net/manual/en/serializable.serialize.php
     * @return string the string representation of the object or null
     * @since 5.1.0
     */
    public function serialize()
    {
        return serialize(
            array($this->shipmentUuid, $this->orderId, $this->orderNumber, $this->parcelId, $this->timestamp)
        );
    }

    /**
     * Constructs the object
     *
     * @param string $serialized
     */
    public function unserialize($serialized)
    {
        list($this->shipmentUuid, $this->orderId, $this->orderNumber, $this->parcelId, $this->timestamp) = unserialize(
            $serialized
        );
    }

    /**
     * Runs task logic
     *
     * @throws HttpAuthenticationException
     * @throws HttpCommunicationException
     */
    public function execute()
    {
        try {
            $parcel = $this->getProxy()->getParcelById($this->parcelId);
            $status = isset($parcel['status']['message']) ? $parcel['status']['message'] : null;
            $statusId = isset($parcel['status']['id']) ? $parcel['status']['id'] : null;
        } catch (HttpRequestException $e) {
            $status = self::SENDCLOUD_STATUS_CANCELLED;
            $statusId = self::SENDCLOUD_STATUS_CANCELLED_ID;
        }

        $this->reportProgress(30);
        $order = $this->getOrderService()->getOrderByNumber($this->orderNumber);
        $this->reportProgress(60);

        if (!empty($order)) {
            $order->setSendCloudStatus($status);
            $order->setSendCloudStatusId($statusId);
            $order->setSendCloudParcelId($this->parcelId);

            if (!empty($parcel['tracking_number'])) {
                $order->setSendCloudTrackingNumber($parcel['tracking_number']);
            }

            if (!empty($parcel['tracking_url'])) {
                $order->setSendCloudTrackingUrl($parcel['tracking_url']);
            }

            if (!empty($parcel['carrier']['code'])) {
                $order->setSendCloudCarrierCode($parcel['carrier']['code']);
            }

            if (!empty($parcel['external_shipment_id'])) {
                $order->setShipmentId($parcel['external_shipment_id']);
            }

            $order->setUpdatedAt(\DateTime::createFromFormat("U.u", (float)$this->timestamp / 1000));
            $this->getOrderService()->updateOrderStatus($order);
        } else {
            Logger::logError("Order number '{$this->orderNumber}' not found in the system.'");
        }

        $this->reportProgress(100);
    }
}
