<?php

namespace Scue\Menu\Api\Data;

interface BannerSearchResultsInterface
{
    /**
     * Get banners list.
     *
     * @return BannerInterface[]
     */
    public function getItems();

    /**
     * Set banners list.
     *
     * @param BannerInterface[] $items
     * @return BannerSearchResultsInterface
     */
    public function setItems(array $items);
}
