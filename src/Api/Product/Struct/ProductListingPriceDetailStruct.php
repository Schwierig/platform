<?php declare(strict_types=1);

namespace Shopware\Api\Product\Struct;

class ProductListingPriceDetailStruct extends ProductListingPriceBasicStruct
{
    /**
     * @var ProductBasicStruct
     */
    protected $product;

    public function getProduct(): ProductBasicStruct
    {
        return $this->product;
    }

    public function setProduct(ProductBasicStruct $product): void
    {
        $this->product = $product;
    }
}