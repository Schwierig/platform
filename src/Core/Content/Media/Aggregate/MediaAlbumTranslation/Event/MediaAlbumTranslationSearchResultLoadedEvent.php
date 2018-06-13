<?php declare(strict_types=1);

namespace Shopware\Core\Content\Media\Aggregate\MediaAlbumTranslation\Event;

use Shopware\Core\Framework\Context;
use Shopware\Core\Content\Media\Aggregate\MediaAlbumTranslation\Struct\MediaAlbumTranslationSearchResult;
use Shopware\Core\Framework\Event\NestedEvent;

class MediaAlbumTranslationSearchResultLoadedEvent extends NestedEvent
{
    public const NAME = 'media_album_translation.search.result.loaded';

    /**
     * @var MediaAlbumTranslationSearchResult
     */
    protected $result;

    public function __construct(MediaAlbumTranslationSearchResult $result)
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