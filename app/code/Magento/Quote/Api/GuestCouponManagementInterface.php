<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Quote\Api;

/**
 * Coupon management interface for guest carts.
 */
interface GuestCouponManagementInterface
{
    /**
     * Returns information for a coupon in a specified cart.
     *
     * @param string $cartId The cart ID.
     * @return string The coupon code data.
     * @throws \Magento\Framework\Exception\NoSuchEntityException The specified cart does not exist.
     */
    public function get($cartId);

    /**
     * Adds a coupon by code to a specified cart.
     *
     * @param string $cartId The cart ID.
     * @param string $couponCode The coupon code data.
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException The specified cart does not exist.
     * @throws \Magento\Framework\Exception\CouldNotSaveException The specified coupon could not be added.
     */
    public function set($cartId, $couponCode);

    /**
     * Deletes a coupon from a specified cart.
     *
     * @param string $cartId The cart ID.
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException The specified cart does not exist.
     * @throws \Magento\Framework\Exception\CouldNotDeleteException The specified coupon could not be deleted.
     */
    public function remove($cartId);
}
