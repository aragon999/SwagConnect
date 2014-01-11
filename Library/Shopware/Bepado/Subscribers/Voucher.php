<?php

namespace Shopware\Bepado\Subscribers;

/**
 * Handle vouchers, remove discounts and don't allow percentaged vouchers for bepado baskets
 *
 * Class Voucher
 * @package Shopware\Bepado\Subscribers
 */
class Voucher extends BaseSubscriber
{

    public function getSubscribedEvents()
    {
        return array(
            'Shopware_Modules_Basket_AddVoucher_Start' => 'preventPercentagedVoucher',
            'Shopware_Modules_Basket_GetBasket_FilterSQL' => 'removeDiscount'
        );
    }

    /**
     * Helper method to remove percentaged discounts from the basket if bepado products are available
     *
     * Alternative:
     *  * Calculate discount only for the default products
     *  * Removed percentaged discounts only if bepado product has a fixedPrice
     *
     * @event Shopware_Modules_Basket_GetBasket_FilterSQL
     */
    public function removeDiscount(\Enlight_Event_EventArgs $args)
    {
        $message = Shopware()->Snippets()->getNamespace('frontend/bepado/checkout')->get(
            'noPercentagedDiscountsAllowed',
            'In Kombination mit bepado-Produkten sind keine prozentualen Rabatte möglich.',
            true
        );

        if ($this->getHelper()->hasBasketBepadoProducts(Shopware()->SessionID())) {
            $stmt = Shopware()->Db()->query(
                "DELETE FROM s_order_basket WHERE sessionID=? AND modus=3",
                array(Shopware()->SessionID())
            );

            // If rows where actually affected, show the corresponding message
            if ($stmt->rowCount()) {
                Shopware()->Template()->assign('sVoucherError', $message);
            }
        }
    }

    /**
     * Will not allow percentaged vouchers if bepado products are in the basket
     *
     * @event Shopware_Modules_Basket_AddVoucher_Start
     *
     * @param \Enlight_Event_EventArgs $args
     * @return bool|null
     */
    public function preventPercentagedVoucher(\Enlight_Event_EventArgs $args)
    {
        $code = $args->getCode();

        if (!$this->getHelper()->hasBasketBepadoProducts(Shopware()->SessionID())) {
            return null;
        }

        $basketHelper = $this->getBasketHelper();

        $message = Shopware()->Snippets()->getNamespace('frontend/bepado/checkout')->get(
            'noPercentagedVoucherAllowed',
            'In Kombination mit bepado-Produkten sind keine prozentualen Gutscheine möglich.',
            true
        );

        // Exclude general percentaged vouchers
        $result = $basketHelper->findPercentagedVouchers($code);
        if (!empty($result)) {
            Shopware()->Template()->assign('sVoucherError', $message);
            return true;
        }

        // Exclude individual percentaged vouchers
        $result = $basketHelper->findPercentagedIndividualVouchers($code);
        if (!empty($result)) {
            Shopware()->Template()->assign('sVoucherError', $message);
            return true;
        }
    }
}