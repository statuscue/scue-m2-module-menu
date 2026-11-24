<?php

declare(strict_types=1);

namespace Scue\Menu\Ui\Component\Banner\Form\Item;

use Scue\Menu\Model\Item\Tree\Builder;
use Scue\Menu\Model\Item\Tree\BuilderFactory as MenuItemTreeBuilderFactory;
use Scue\Menu\Model\ResourceModel\Item\CollectionFactory as MenuItemCollectionFactory;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\Data\Tree\Node;
use Magento\Store\Model\StoreManagerInterface;

class OptionsByStoreView implements OptionSourceInterface
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
            $stores = $this->storeManager->getStores(false, true);
            foreach ($stores as $code => $store) {
                if (!$store->isActive()) {
                    continue;
                }

                $this->options[] = [
                    'value' => $code,
                    'label' => $store->getName(),
                    'optgroup' => $this->getMenuItemsByStoreId((int)$store->getId())
                ];
            }
        }

        return $this->options;
    }

    private function getMenuItemsByStoreId(int $storeId): array
    {
        $tree = $this->getMenuItemsTreeByStoreId($storeId);
        $nodes = $this->getMenuItemNodesByLevel($tree, Builder::LEVEL_TAB);
        $options = [];
        /** @var Node $node */
        foreach ($nodes as $node) {
            $options[] = [
                'label' => \sprintf('%s (#%s)', $node->getData('title'), $node->getId()),
                'value' => $node->getId(),
            ];
        }

        return $options;
    }

    private function getMenuItemNodesByLevel(Node $tree, int $level, array &$nodes = []): array
    {
        /** @var Node $node */
        foreach ($tree->getChildren() as $node) {
            $nodeLevel = (int)$node->getData('level');
            if ($nodeLevel === $level) {
                // What are we looking for
                $nodes[] = $node;
            } elseif ($nodeLevel < $level) {
                // Run again to get children
                $this->getMenuItemNodesByLevel($node, $level, $nodes);
            } elseif ($nodeLevel > $level) {
                // Too far..
                return $nodes;
            }
        }

        return $nodes;
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
}
