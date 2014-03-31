<?php

namespace Shopware\Bepado\Components\Utils;

use Bepado\SDK\Struct\Order as OrderStruct;

/**
 * Class OrderPaymentMapper
 * @package Shopware\Bepado\Components\Utils
 */
class OrderPaymentMapper
{
    private $mapping;

    /**
     * Returns an array of mappings from sw order payment to bepado order states
     *
     * Can be modified and extended by using the Bepado_OrderPayment_Mapping filter event
     *
     * @return mixed
     */
    protected function getMapping()
    {
        if (!$this->mapping) {
            $this->mapping = array(
                'debit' => OrderStruct::PAYMENT_DEBIT,
                'cash' => OrderStruct::PAYMENT_OTHER,
                'invoice' => OrderStruct::PAYMENT_INVOICE,
                'prepayment' => OrderStruct::PAYMENT_ADVANCE,
                'sepa' => OrderStruct::PAYMENT_DEBIT,
                'paypal' => OrderStruct::PAYMENT_PROVIDER,
            );

            $this->mapping = Enlight()->Events()->filter('Bepado_OrderStatus_Mapping', $this->mapping);
        }

        return $this->mapping;
    }

    /**
     * Helper to map shopware order payment to bepado order states
     *
     * @param $swOrderPayment
     * @return string
     */
    public function mapShopwareOrderPaymentToBepado($swOrderPayment)
    {
        $swOrderPayment = (string)$swOrderPayment;

        $mapping = $this->getMapping();

        if (!array_key_exists($swOrderPayment, $mapping)) {
            return OrderStruct::PAYMENT_UNKNOWN;
        }

        return $mapping[$swOrderPayment];
    }
} 