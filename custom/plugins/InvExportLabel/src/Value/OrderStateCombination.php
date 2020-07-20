<?php declare(strict_types=1);


namespace InvExportLabel\Value;

use Shopware\Core\Checkout\Order\Aggregate\OrderDelivery\OrderDeliveryStates;
use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionStates;
use Shopware\Core\Checkout\Order\OrderStates;

/**
 * Class OrderStateCombination
 * @package InvExportLabel\Value
 */
final class OrderStateCombination
{

    /**
     * @var string|null
     */
    private $orderState;

    /**
     * @var string|null
     */
    private $transactionState;

    /**
     * @var string|null
     */
    private $deliveryState;

    /**
     * OrderStateCombination constructor.
     * @param string|null $orderState
     * @param string|null $transactionState
     * @param string|null $deliveryState
     */
    public function __construct(?string $orderState, ?string $transactionState, ?string $deliveryState)
    {
        $this->orderState = $orderState;
        $this->transactionState = $transactionState;
        $this->deliveryState = $deliveryState;
    }


    public static function fromConfigValue(string $value): self
    {

        $elements = \json_decode($value, true);

        if (!$elements || empty($elements)) {
            return new self(
                OrderStates::STATE_CANCELLED,
                OrderTransactionStates::STATE_CANCELLED,
                OrderDeliveryStates::STATE_CANCELLED
            );
        }

        return new self(
            $elements['order'],
            $elements['orderTransaction'],
            $elements['orderDelivery']
        );
    }

    /**
     * @return string|null
     */
    public function getOrderState(): ?string
    {
        return $this->orderState;
    }

    /**
     * @return string|null
     */
    public function getTransactionState(): ?string
    {
        return $this->transactionState;
    }

    /**
     * @return string|null
     */
    public function getDeliveryState(): ?string
    {
        return $this->deliveryState;
    }




}
