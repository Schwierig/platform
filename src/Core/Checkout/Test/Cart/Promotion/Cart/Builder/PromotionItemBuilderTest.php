<?php declare(strict_types=1);

namespace Shopware\Core\Checkout\Test\Cart\Promotion\Cart\Builder;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Checkout\Cart\Exception\InvalidPayloadException;
use Shopware\Core\Checkout\Cart\Exception\InvalidQuantityException;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\Price\Struct\AbsolutePriceDefinition;
use Shopware\Core\Checkout\Cart\Price\Struct\PercentagePriceDefinition;
use Shopware\Core\Checkout\Cart\Rule\LineItemRule;
use Shopware\Core\Checkout\Promotion\Cart\Builder\PromotionItemBuilder;
use Shopware\Core\Checkout\Promotion\PromotionEntity;
use Shopware\Core\Framework\Rule\Rule;

class PromotionItemBuilderTest extends TestCase
{
    /**
     * @var PromotionEntity
     */
    private $promotion = null;

    public function setUp(): void
    {
        $this->promotion = new PromotionEntity();
        $this->promotion->setId('PR-1');
    }

    /**
     * This test verifies that the immutable LineItem Type from
     * the constructor is correctly used in the LineItem.
     *
     * @test
     * @group promotions
     *
     * @throws InvalidPayloadException
     * @throws InvalidQuantityException
     */
    public function testLineItemType()
    {
        $builder = new PromotionItemBuilder('My-TYPE');

        $this->promotion->setPercental(true);
        $this->promotion->setValue(10);

        $item = $builder->buildPromotionItem($this->promotion, 1, []);

        static::assertEquals('My-TYPE', $item->getType());
    }

    /**
     * This test verifies that our LineItem contains a payload
     * with all product item IDs that are eligible for
     * the current promotion entity.
     * Every payload entry is also prefixed with "item-".
     *
     * @test
     * @group promotions
     *
     * @throws InvalidPayloadException
     * @throws InvalidQuantityException
     */
    public function testPayloadContainsProductItems()
    {
        $builder = new PromotionItemBuilder('My-TYPE');

        $this->promotion->setPercental(true);
        $this->promotion->setValue(10);

        $item = $builder->buildPromotionItem($this->promotion, 1, ['P1', 'P2']);

        $expectedPayload = [
            'item-P1' => 'P1',
            'item-P2' => 'P2',
            'code' => '',
        ];

        static::assertEquals($expectedPayload, $item->getPayload());
    }

    /**
     * This test verifies that our payload does also contain
     * the code property which allows us to identify the entered
     * code later on.
     *
     * @test
     * @group promotions
     *
     * @throws InvalidPayloadException
     * @throws InvalidQuantityException
     */
    public function testPayloadContainsCode()
    {
        $builder = new PromotionItemBuilder('My-TYPE');

        $this->promotion->setPercental(true);
        $this->promotion->setValue(10);
        $this->promotion->setCode('CODE-123');

        $item = $builder->buildPromotionItem($this->promotion, 1, ['P1', 'P2']);

        static::assertEquals('CODE-123', $item->getPayload()['code']);
    }

    /**
     * This test verifies that we get a correct percentage price
     * definition if our promotion is based on percentage values.
     * Also, we must not have a filter rule for this, if our eligible item ID list is empty.
     *
     * @test
     * @group promotions
     *
     * @throws InvalidPayloadException
     * @throws InvalidQuantityException
     */
    public function testPercentagePriceDefinition()
    {
        $builder = new PromotionItemBuilder('My-TYPE');

        $this->promotion->setPercental(true);
        $this->promotion->setValue(10);

        $item = $builder->buildPromotionItem($this->promotion, 1, []);

        $expectedPriceDefinition = new PercentagePriceDefinition(-10, 1);

        static::assertEquals($expectedPriceDefinition, $item->getPriceDefinition());
    }

    /**
     * This test verifies that we get a correct absolute price
     * definition if our promotion is based on absolute values.
     * Also, we must not have a filter rule for this, if our eligible item ID list is empty.
     *
     * @test
     * @group promotions
     *
     * @throws InvalidPayloadException
     * @throws InvalidQuantityException
     */
    public function testAbsolutePriceDefinition()
    {
        $builder = new PromotionItemBuilder('My-TYPE');

        $this->promotion->setPercental(false);
        $this->promotion->setValue(50);

        $item = $builder->buildPromotionItem($this->promotion, 1, []);

        $expectedPriceDefinition = new AbsolutePriceDefinition(-50, 1);

        static::assertEquals($expectedPriceDefinition, $item->getPriceDefinition());
    }

    /**
     * This test verifies that our price definition gets the
     * correct filter with the values from our eligible item IDs.
     * With this filter, the calculation process will only apply
     * discounts on these provided item IDs from our promotion.
     *
     * @test
     * @group promotions
     *
     * @throws InvalidPayloadException
     * @throws InvalidQuantityException
     */
    public function testEligibleItemsGetDiscount()
    {
        $builder = new PromotionItemBuilder('My-TYPE');

        $this->promotion->setPercental(false);
        $this->promotion->setValue(50);

        $item = $builder->buildPromotionItem($this->promotion, 1, ['P1', 'P2']);

        $itemRule = new LineItemRule();
        $itemRule->assign(['identifiers' => ['P1', 'P2'], 'operator' => Rule::OPERATOR_EQ]);
        $expectedPriceDefinition = new AbsolutePriceDefinition(-50, 1, $itemRule);

        static::assertEquals($expectedPriceDefinition, $item->getPriceDefinition());
    }

    /**
     * This test verifies that the correct priority value
     * is being used for the promotion line item.
     * This should be the voucher constant from the line items.
     *
     * @test
     * @group promotions
     *
     * @throws InvalidPayloadException
     * @throws InvalidQuantityException
     */
    public function testPriority()
    {
        $builder = new PromotionItemBuilder('My-TYPE');

        $this->promotion->setPercental(false);
        $this->promotion->setValue(50);

        $item = $builder->buildPromotionItem($this->promotion, 1, []);

        static::assertEquals(LineItem::VOUCHER_PRIORITY, $item->getPriority());
    }
}
