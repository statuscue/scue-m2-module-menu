<?php

declare(strict_types=1);

namespace Scue\Menu\Ui\Component\Form\Items;

use Scue\Menu\Model\Item;
use Scue\Menu\Model\Item\Tree\BuilderFactory as MenuItemTreeBuilderFactory;
use Scue\Menu\Model\ResourceModel\Item\CollectionFactory as MenuItemCollectionFactory;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\Data\Tree\Node;
use Magento\Store\Model\StoreManagerInterface;

class Options implements OptionSourceInterface
{
    private StoreManagerInterface $storeManager;
    private MenuItemCollectionFactory $menuItemCollectionFactory;
    private MenuItemTreeBuilderFactory $treeBuilderFactory;
    private array $options = [];

    public function __construct(
        StoreManagerInterface $storeManager,
        MenuItemCollectionFactory $menuItemCollectionFactory,
        MenuItemTreeBuilderFactory $treeBuilderFactory
    ) {
        $this->menuItemCollectionFactory = $menuItemCollectionFactory;
        $this->treeBuilderFactory = $treeBuilderFactory;
        $this->storeManager = $storeManager;
    }

    /**
     * @inheritDoc
     */
    public function toOptionArray()
    {
        if (empty($this->options)) {
            // Add "No parent" option to the most top, because it seems that component
            // does not allow to reset or delete already selected value.
            $this->options[] = [
                'label' => __('-- no parent --'),
                'value' => Item::NO_PARENT_ID
            ];

            $stores = $this->storeManager->getStores(false, true);
            foreach ($stores as $code => $store) {
                if (!$store->isActive()) {
                    continue;
                }

                $this->options[] = [
                    'value' => $code,
                    'label' => $store->getName(),
                    'optgroup' => $this->getOptionsTreeByStoreId((int)$store->getId())
                ];
            }
        }
        return $this->options;
    }

    private function getOptionsTreeByStoreId(int $storeId): array
    {
        $tree = $this->getMenuItemsTreeByStoreId($storeId);
        return $this->buildOptionsTree($tree);
    }

    private function getMenuItemsTreeByStoreId(int $storeId): Node
    {
        $collection = $this->menuItemCollectionFactory->create();
        $collection
            ->addFieldToSelect(['title', 'parent_id'])
            ->addFieldToFilter('store_id', $storeId)
            ->setOrder('menu_item_id', 'ASC')
            ->setOrder('sort_order', 'ASC');

        $treeBuilder = $this->treeBuilderFactory->create();
        return $treeBuilder->setCollection($collection)->build();
    }

    /**
     * Build a tree structure suitable for uiSelect component.
     *
     * @param Node $parentNode
     * @return array
     */
    private function buildOptionsTree(Node $parentNode): array
    {
        $options = [];
        /** @var Node $node */
        foreach ($parentNode->getChildren() as $node) {
            $option = [
                'label' => $node->getData('title'),
                'value' => $node->getId(),
            ];

            if ($node->hasChildren()) {
                $option['optgroup'] = $this->buildOptionsTree($node);
            }

            $options[] = $option;
        }

        return $options;
    }
}
