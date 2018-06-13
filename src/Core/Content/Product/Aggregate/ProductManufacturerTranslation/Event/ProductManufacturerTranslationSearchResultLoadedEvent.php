<?php declare(strict_types=1);

namespace Shopware\Core\Content\Product\Aggregate\ProductManufacturerTranslation\Event;

use Shopware\Core\Framework\Context;
use Shopware\Core\Content\Product\Aggregate\ProductManufacturerTranslation\Struct\ProductManufacturerTranslationSearchResult;
use Shopware\Core\Framework\Event\NestedEvent;

class ProductManufacturerTranslationSearchResultLoadedEvent extends NestedEvent
{
    public const NAME = 'product_manufacturer_translation.search.result.loaded';

    /**
     * @var ProductManufacturerTranslationSearchResult
     */
    protected $result;

    public function __construct(ProductManufacturerTranslationSearchResult $result)
    {
        $this->result = $result;
    }

    public function getName(): string
    {
        return self::NAME;
    }

    public function getContext(): Context
    {
        return $this->result->getContext();
    }
}