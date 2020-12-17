<?php declare(strict_types=1);


namespace InvExportLabel\Value;

/**
 * Interface SourceItemInterface
 * @package InvExportLabel\Value
 */
interface SourceItemInterface {


    /**
     * @return string
     */
    public function getOrderNumber(): string;

    /**
     * @param string $orderNumber
     * @return SourceItemInterface
     */
    public function setOrderNumber(string $orderNumber): SourceItemInterface;

}
