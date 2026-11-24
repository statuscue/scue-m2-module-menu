<?php

declare(strict_types=1);

namespace Scue\Menu\Model;

use Scue\Menu\Api\BannerRepositoryInterface;
use Scue\Menu\Api\Data\BannerInterface;
use Scue\Menu\Api\Data\BannerSearchResultsInterface;
use Scue\Menu\Model\ResourceModel\Banner as BannerResource;
use Scue\Menu\Model\ResourceModel\Banner\CollectionFactory as BannerCollectionFactory;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;

class BannerRepository implements BannerRepositoryInterface
{
    private FilterBuilder $filterBuilder;
    private SearchCriteriaBuilder $searchCriteriaBuilder;
    private CollectionProcessorInterface $collectionProcessor;
    private BannerFactory $bannerFactory;
    private BannerResource $bannerResource;
    private BannerCollectionFactory $bannerCollectionFactory;
    private BannerSearchResultsFactory $bannerSearchResultsFactory;

    public function __construct(
        FilterBuilder $filterBuilder,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        CollectionProcessorInterface $collectionProcessor,
        BannerFactory $bannerFactory,
        BannerResource $bannerResource,
        BannerCollectionFactory $bannerCollectionFactory,
        BannerSearchResultsFactory $bannerSearchResultsFactory
    ) {
        $this->filterBuilder = $filterBuilder;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->collectionProcessor = $collectionProcessor;
        $this->bannerFactory = $bannerFactory;
        $this->bannerResource = $bannerResource;
        $this->bannerCollectionFactory = $bannerCollectionFactory;
        $this->bannerSearchResultsFactory = $bannerSearchResultsFactory;
    }

    /**
     * @inheritDoc
     */
    public function getById(int $id): BannerInterface
    {
        $banner = $this->bannerFactory->create();
        $this->bannerResource->load($banner, $id);
        if (!$banner->getId()) {
            throw new NoSuchEntityException(
                __("The banner that was requested doesn't exist. Verify the banner and try again.")
            );
        }

        return $banner;
    }

    /**
     * @inheritDoc
     */
    public function getByMenuItemId(int $menuItemId): array
    {
        $filter = $this->filterBuilder
            ->setField(BannerInterface::MENU_ITEM_ID)
            ->setConditionType('eq')
            ->setValue($menuItemId)
            ->create();

        $searchCriteria = $this->searchCriteriaBuilder->addFilters([$filter])->create();
        $items = $this->getList($searchCriteria)->getItems();

        if (empty($items)) {
            throw new NoSuchEntityException(__("Couldn't find banner(s) attached to menu item ID #%1", $menuItemId));
        }

        return $items;
    }

    /**
     * @inheritDoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria): BannerSearchResultsInterface
    {
        $collection = $this->bannerCollectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, $collection);

        $results = $this->bannerSearchResultsFactory->create();
        $results->setSearchCriteria($searchCriteria);
        $results->setItems($collection->getItems());
        $results->setTotalCount($collection->getSize());

        return $results;
    }

    /**
     * @inheritDoc
     */
    public function save(BannerInterface $banner): BannerInterface
    {
        $this->bannerResource->save($banner);
        // TODO: maybe this could be better?
        return $banner->getId() ? $this->getById($banner->getId()) : $banner;
    }

    /**
     * @inheritDoc
     */
    public function delete(BannerInterface $banner): bool
    {
        $bannerId = $banner->getId();
        try {
            $this->bannerResource->delete($banner);
        } catch (\Exception $e) {
            throw new StateException(__("The banner #%1 couldn't be removed", $bannerId), $e);
        }
        return true;
    }

    /**
     * @inheritDoc
     */
    public function deleteById(int $id): bool
    {
        try {
            $banner = $this->getById($id);
            return $this->delete($banner);
        } catch (NoSuchEntityException $e) {
            throw new StateException(__("Couldn't delete banner #%1, because it doesn't exists.", $id), $e);
        }
    }
}
