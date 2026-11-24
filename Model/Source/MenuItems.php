<?php

declare(strict_types=1);

namespace Scue\Menu\Model\Source;

use Scue\Menu\Model\Item;
use Scue\Menu\Model\ResourceModel\Item\Collection;
use Scue\Menu\Model\ResourceModel\Item\CollectionFactory;

class MenuItems implements \Magento\Framework\Data\OptionSourceInterface
{
    private Collection $collection;
    private array $loadedData = [];

    public function __construct(
        CollectionFactory $collectionFactory
    ) {
        $this->collection = $collectionFactory->create();
    }

    /**
     * @inheritDoc
     */
    public function toOptionArray()
    {
        if (!empty($this->loadedData)) {
            return $this->loadedData;
        }

        /** @var Item $menuItem */
        foreach ($this->collection as $menuItem) {
            $this->loadedData[] = [
                'value' => $menuItem->getId(),
                'label' => $menuItem->getTitle(),
            ];
        }

        return $this->loadedData;
    }
}
