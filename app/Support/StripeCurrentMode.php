<?php

namespace App\Support;

use Stripe\Coupon;
use Stripe\Exception\InvalidRequestException;
use Stripe\Product;

class StripeCurrentMode
{
    public static function existingProductId(?string $productId): ?string
    {
        $productId = trim((string) $productId);
        if ($productId === '') {
            return null;
        }

        try {
            Product::retrieve($productId);

            return $productId;
        } catch (InvalidRequestException) {
            return null;
        }
    }

    public static function existingCouponId(?string $couponId): ?string
    {
        $couponId = trim((string) $couponId);
        if ($couponId === '') {
            return null;
        }

        try {
            Coupon::retrieve($couponId);

            return $couponId;
        } catch (InvalidRequestException) {
            return null;
        }
    }
}
