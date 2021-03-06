<?php declare(strict_types=1);

namespace Shopware\Core\Framework\Test\Api\Controller;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\Test\TestCaseBase\AdminFunctionalTestBehaviour;
use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class CacheControllerTest extends TestCase
{
    use AdminFunctionalTestBehaviour;

    /**
     * @var TagAwareAdapterInterface
     */
    private $cache;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cache = $this->getContainer()->get('shopware.cache');

        $item = $this->cache->getItem('foo');
        $item->set('bar');
        $item->tag(['foo-tag']);
        $this->cache->save($item);

        $item = $this->cache->getItem('bar');
        $item->set('foo');
        $item->tag(['bar-tag']);
        $this->cache->save($item);
    }

    public function testClearCacheEndpoint(): void
    {
        static::assertTrue($this->cache->getItem('foo')->isHit());
        static::assertTrue($this->cache->getItem('bar')->isHit());

        $this->getClient()->request('DELETE', '/api/v1/_action/cache');

        /** @var JsonResponse $response */
        $response = $this->getClient()->getResponse();

        static::assertSame(Response::HTTP_OK, $response->getStatusCode(), print_r($response->getContent(), true));

        static::assertFalse($this->cache->getItem('foo')->isHit());
        static::assertFalse($this->cache->getItem('bar')->isHit());
    }

    public function testClearCacheItems(): void
    {
        static::assertTrue($this->cache->getItem('foo')->isHit());
        static::assertTrue($this->cache->getItem('bar')->isHit());

        $ids = ['foo', 'bar'];

        $this->getClient()->request('DELETE', '/api/v1/_action/cache/item', [], [], [], json_encode($ids));

        /** @var JsonResponse $response */
        $response = $this->getClient()->getResponse();

        static::assertSame(Response::HTTP_OK, $response->getStatusCode(), print_r($response->getContent(), true));

        static::assertFalse($this->cache->getItem('foo')->isHit());
        static::assertFalse($this->cache->getItem('bar')->isHit());
    }

    public function testClearCacheWithInvalidParameters(): void
    {
        $ids = [1, 2, 3];

        /* @var JsonResponse $response */
        $this->getClient()->request('DELETE', '/api/v1/_action/cache/item', [], [], [], json_encode($ids));
        $response = $this->getClient()->getResponse();
        static::assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode(), print_r($response->getContent(), true));

        $ids = [];

        /* @var JsonResponse $response */
        $this->getClient()->request('DELETE', '/api/v1/_action/cache/item', [], [], [], json_encode($ids));
        $response = $this->getClient()->getResponse();
        static::assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode(), print_r($response->getContent(), true));

        $ids = ['test', true];
        /* @var JsonResponse $response */
        $this->getClient()->request('DELETE', '/api/v1/_action/cache/item', [], [], [], json_encode($ids));
        $response = $this->getClient()->getResponse();
        static::assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode(), print_r($response->getContent(), true));
    }

    public function testInvalidateTags(): void
    {
        static::assertTrue($this->cache->getItem('foo')->isHit());
        static::assertTrue($this->cache->getItem('bar')->isHit());

        $ids = ['foo-tag', 'bar-tag'];

        $this->getClient()->request('DELETE', '/api/v1/_action/cache/tag', [], [], [], json_encode($ids));

        /** @var JsonResponse $response */
        $response = $this->getClient()->getResponse();

        static::assertSame(Response::HTTP_OK, $response->getStatusCode(), print_r($response->getContent(), true));

        static::assertFalse($this->cache->getItem('foo')->isHit());
        static::assertFalse($this->cache->getItem('bar')->isHit());
    }

    public function testInvalidateTagsWithInvalidParameters(): void
    {
        $ids = [1, 2, 3];

        /* @var JsonResponse $response */
        $this->getClient()->request('DELETE', '/api/v1/_action/cache/tag', [], [], [], json_encode($ids));
        $response = $this->getClient()->getResponse();
        static::assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode(), print_r($response->getContent(), true));

        $ids = [];

        /* @var JsonResponse $response */
        $this->getClient()->request('DELETE', '/api/v1/_action/cache/tag', [], [], [], json_encode($ids));
        $response = $this->getClient()->getResponse();
        static::assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode(), print_r($response->getContent(), true));

        $ids = ['test', true];
        /* @var JsonResponse $response */
        $this->getClient()->request('DELETE', '/api/v1/_action/cache/tag', [], [], [], json_encode($ids));
        $response = $this->getClient()->getResponse();
        static::assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode(), print_r($response->getContent(), true));
    }
}
