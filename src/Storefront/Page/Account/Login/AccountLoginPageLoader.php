<?php declare(strict_types=1);

namespace Shopware\Storefront\Page\Account\Login;

use Shopware\Core\Checkout\Customer\Storefront\AccountService;
use Shopware\Core\Checkout\Customer\Storefront\AddressService;
use Shopware\Core\Framework\Routing\InternalRequest;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Framework\Page\PageLoaderInterface;
use Shopware\Storefront\Framework\Page\PageWithHeaderLoader;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class AccountLoginPageLoader implements PageLoaderInterface
{
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var PageWithHeaderLoader|PageLoaderInterface
     */
    private $pageWithHeaderLoader;

    /**
     * @var AddressService
     */
    private $addressService;

    /**
     * @var AccountService
     */
    private $accountService;

    public function __construct(
        PageLoaderInterface $pageWithHeaderLoader,
        AddressService $addressService,
        AccountService $accountService,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->pageWithHeaderLoader = $pageWithHeaderLoader;
        $this->addressService = $addressService;
        $this->accountService = $accountService;
    }

    public function load(InternalRequest $request, SalesChannelContext $context): AccountLoginPage
    {
        $page = $this->pageWithHeaderLoader->load($request, $context);

        $page = AccountLoginPage::createFrom($page);

        $page->setCountries($this->addressService->getCountryList($context));
        $page->setSalutations($this->accountService->getSalutationList($context));

        $this->eventDispatcher->dispatch(
            AccountLoginPageLoadedEvent::NAME,
            new AccountLoginPageLoadedEvent($page, $context, $request)
        );

        return $page;
    }
}
