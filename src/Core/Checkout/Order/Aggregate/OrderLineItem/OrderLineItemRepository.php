<?php declare(strict_types=1);

namespace Shopware\Core\Checkout\Order\Aggregate\OrderLineItem;

use Shopware\Core\Framework\Context;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\Collection\OrderLineItemBasicCollection;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\Collection\OrderLineItemDetailCollection;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\Event\OrderLineItemAggregationResultLoadedEvent;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\Event\OrderLineItemBasicLoadedEvent;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\Event\OrderLineItemDetailLoadedEvent;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\Event\OrderLineItemIdSearchResultLoadedEvent;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\Event\OrderLineItemSearchResultLoadedEvent;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\Struct\OrderLineItemSearchResult;
use Shopware\Core\Framework\ORM\Read\EntityReaderInterface;
use Shopware\Core\Framework\ORM\RepositoryInterface;
use Shopware\Core\Framework\ORM\Search\AggregatorResult;
use Shopware\Core\Framework\ORM\Search\Criteria;
use Shopware\Core\Framework\ORM\Search\EntityAggregatorInterface;
use Shopware\Core\Framework\ORM\Search\EntitySearcherInterface;
use Shopware\Core\Framework\ORM\Search\IdSearchResult;
use Shopware\Core\Framework\ORM\Version\Service\VersionManager;
use Shopware\Core\Framework\ORM\Write\GenericWrittenEvent;
use Shopware\Core\Framework\ORM\Write\WriteContext;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class OrderLineItemRepository implements RepositoryInterface
{
    /**
     * @var EntityReaderInterface
     */
    private $reader;

    /**
     * @var EntitySearcherInterface
     */
    private $searcher;

    /**
     * @var EntityAggregatorInterface
     */
    private $aggregator;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var \Shopware\Core\Framework\ORM\Version\Service\VersionManager
     */
    private $versionManager;

    public function __construct(
       EntityReaderInterface $reader,
       VersionManager $versionManager,
       EntitySearcherInterface $searcher,
       EntityAggregatorInterface $aggregator,
       EventDispatcherInterface $eventDispatcher
   ) {
        $this->reader = $reader;
        $this->searcher = $searcher;
        $this->aggregator = $aggregator;
        $this->eventDispatcher = $eventDispatcher;
        $this->versionManager = $versionManager;
    }

    public function search(Criteria $criteria, Context $context): OrderLineItemSearchResult
    {
        $ids = $this->searchIds($criteria, $context);

        $entities = $this->readBasic($ids->getIds(), $context);

        $aggregations = null;
        if ($criteria->getAggregations()) {
            $aggregations = $this->aggregate($criteria, $context);
        }

        $result = OrderLineItemSearchResult::createFromResults($ids, $entities, $aggregations);

        $event = new OrderLineItemSearchResultLoadedEvent($result);
        $this->eventDispatcher->dispatch($event->getName(), $event);

        return $result;
    }

    public function aggregate(Criteria $criteria, Context $context): AggregatorResult
    {
        $result = $this->aggregator->aggregate(OrderLineItemDefinition::class, $criteria, $context);

        $event = new OrderLineItemAggregationResultLoadedEvent($result);
        $this->eventDispatcher->dispatch($event->getName(), $event);

        return $result;
    }

    public function searchIds(Criteria $criteria, Context $context): IdSearchResult
    {
        $result = $this->searcher->search(OrderLineItemDefinition::class, $criteria, $context);

        $event = new OrderLineItemIdSearchResultLoadedEvent($result);
        $this->eventDispatcher->dispatch($event->getName(), $event);

        return $result;
    }

    public function readBasic(array $ids, Context $context): OrderLineItemBasicCollection
    {
        /** @var \Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\Collection\OrderLineItemBasicCollection $entities */
        $entities = $this->reader->readBasic(OrderLineItemDefinition::class, $ids, $context);

        $event = new OrderLineItemBasicLoadedEvent($entities, $context);
        $this->eventDispatcher->dispatch($event->getName(), $event);

        return $entities;
    }

    public function readDetail(array $ids, Context $context): OrderLineItemDetailCollection
    {
        /** @var OrderLineItemDetailCollection $entities */
        $entities = $this->reader->readDetail(OrderLineItemDefinition::class, $ids, $context);

        $event = new OrderLineItemDetailLoadedEvent($entities, $context);
        $this->eventDispatcher->dispatch($event->getName(), $event);

        return $entities;
    }

    public function update(array $data, Context $context): GenericWrittenEvent
    {
        $affected = $this->versionManager->update(OrderLineItemDefinition::class, $data, WriteContext::createFromContext($context));
        $event = GenericWrittenEvent::createWithWrittenEvents($affected, $context, []);
        $this->eventDispatcher->dispatch(GenericWrittenEvent::NAME, $event);

        return $event;
    }

    public function upsert(array $data, Context $context): GenericWrittenEvent
    {
        $affected = $this->versionManager->upsert(OrderLineItemDefinition::class, $data, WriteContext::createFromContext($context));
        $event = GenericWrittenEvent::createWithWrittenEvents($affected, $context, []);
        $this->eventDispatcher->dispatch(GenericWrittenEvent::NAME, $event);

        return $event;
    }

    public function create(array $data, Context $context): GenericWrittenEvent
    {
        $affected = $this->versionManager->insert(OrderLineItemDefinition::class, $data, WriteContext::createFromContext($context));
        $event = GenericWrittenEvent::createWithWrittenEvents($affected, $context, []);
        $this->eventDispatcher->dispatch(GenericWrittenEvent::NAME, $event);

        return $event;
    }

    public function delete(array $ids, Context $context): GenericWrittenEvent
    {
        $affected = $this->versionManager->delete(OrderLineItemDefinition::class, $ids, WriteContext::createFromContext($context));
        $event = GenericWrittenEvent::createWithDeletedEvents($affected, $context, []);
        $this->eventDispatcher->dispatch(GenericWrittenEvent::NAME, $event);

        return $event;
    }

    public function createVersion(string $id, Context $context, ?string $name = null, ?string $versionId = null): string
    {
        return $this->versionManager->createVersion(OrderLineItemDefinition::class, $id, WriteContext::createFromContext($context), $name, $versionId);
    }

    public function merge(string $versionId, Context $context): void
    {
        $this->versionManager->merge($versionId, WriteContext::createFromContext($context));
    }
}