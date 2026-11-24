<?php

declare(strict_types=1);

namespace Scue\Menu\Model\Item\Tree;

use Scue\Menu\Model\Item;
use Scue\Menu\Model\ResourceModel\Item\Collection as MenuItemCollection;
use Scue\Menu\Model\ResourceModel\Item\CollectionFactory as MenuItemCollectionFactory;
use Magento\Framework\Data\Tree\Node;
use Magento\Framework\Data\Tree\NodeFactory;
use Magento\Framework\Data\TreeFactory;
use Magento\Store\Model\StoreManagerInterface;

class Builder
{
    public const LEVEL_TOP = 0;
    public const LEVEL_TAB = 1;
    public const LEVEL_COLUMN = 2;
    public const LEVEL_COLUMN_ITEM = 3;

    private TreeFactory $treeFactory;
    private NodeFactory $nodeFactory;
    private StoreManagerInterface $storeManager;
    private MenuItemCollection $collection;
    private MenuItemCollectionFactory $collectionFactory;
    private int $store;
    private Node $tree;

    public function __construct(
        TreeFactory $treeFactory,
        NodeFactory $nodeFactory,
        StoreManagerInterface $storeManager,
        MenuItemCollectionFactory $collectionFactory
    ) {
        $this->treeFactory = $treeFactory;
        $this->nodeFactory = $nodeFactory;
        $this->storeManager = $storeManager;
        $this->collectionFactory = $collectionFactory;
    }

    public function getStoreId(): int
    {
        if (!isset($this->store)) {
            $this->store = (int)$this->storeManager->getStore()->getId();
        }
        return $this->store;
    }

    public function setStoreId(int $store): Builder
    {
        $this->store = $store;
        return $this;
    }

    public function getCollection(): MenuItemCollection
    {
        if (!isset($this->collection)) {
            // Create collection if not set manually
            $this->collection = $this->collectionFactory->create();
            $this->collection
                ->addFieldToFilter('store_id', $this->getStoreId())
                ->setOrder('main_table.sort_order', 'ASC')
                ->setOrder('main_table.menu_item_id', 'ASC');
        }

        return $this->collection;
    }

    public function setCollection(MenuItemCollection $collection): Builder
    {
        $this->collection = $collection;
        return $this;
    }

    public function build(): Node
    {
        if (!isset($this->tree)) {
            $collection = $this->getCollection();
            $rootNode = $this->nodeFactory->create([
                'data' => [],
                'idField' => 'root',
                'tree' => $this->treeFactory->create()
            ]);

            $this->tree = $this->buildTree($collection, $rootNode);
        }
        return $this->tree;
    }

    private function buildTree(
        MenuItemCollection $collection,
        Node $parentNode,
        int $parentNodeId = 0,
        int $level = -1
    ): Node {
        $level++;

        /** @var Item $menuItem */
        foreach ($collection as $menuItem) {
            $menuItemId = (int)$menuItem->getId();
            $menuItemParentId = (int)$menuItem->getParentId();

            if ($menuItemParentId === $parentNodeId) {
                $node = new Node(
                    $this->getMenuItemAsArray($menuItem),
                    $menuItem->getIdFieldName(),
                    $parentNode->getTree(),
                    $parentNode
                );

                $node->setLevel($level);
                $parentNode->addChild($node);

                // Run again to find children
                $this->buildTree($collection, $node, $menuItemId, $level);
            }

            unset($menuItemId, $menuItemParentId);
        }

        return $parentNode;
    }

    private function getMenuItemAsArray(Item $menuItem): array
    {
        return $menuItem->getData();
    }
}
