<?php

namespace Scue\Menu\Api;

use Scue\Menu\Api\Data\BannerInterface;
use Scue\Menu\Api\Data\BannerSearchResultsInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;

interface BannerRepositoryInterface
{
    /**
     * Retrieve banner by id
     *
     * @param int $id
     * @return BannerInterface
     * @throws NoSuchEntityException
     */
    public function getById(int $id): BannerInterface;

    /**
     * Retrieve banner(s) by menu item id
     *
     * @param int $menuItemId
     * @return BannerInterface[]
     * @throws NoSuchEntityException
     */
    public function getByMenuItemId(int $menuItemId): array;

    /**
     * Save banner
     *
     * @param BannerInterface $banner
     * @return BannerInterface
     * @throws AlreadyExistsException
     * @throws CouldNotSaveException
     */
    public function save(BannerInterface $banner): BannerInterface;

    /**
     * Retrieve banners matching the specified criteria
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return BannerSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria): BannerSearchResultsInterface;

    /**
     * Delete banner
     *
     * @param BannerInterface $banner
     * @return bool
     * @throws StateException
     */
    public function delete(BannerInterface $banner): bool;

    /**
     * Delete banner by id
     *
     * @param int $id
     * @return bool
     * @throws StateException
     */
    public function deleteById(int $id): bool;
}
