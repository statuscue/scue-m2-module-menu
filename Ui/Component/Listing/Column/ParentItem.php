<?php

declare(strict_types=1);

namespace Scue\Menu\Ui\Component\Listing\Column;

use Scue\Menu\Model\Item;
use Scue\Menu\Model\ResourceModel\Item\CollectionFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Column "parent" in the admin grid with a list of menu items.
 *
 * Note, that this column should contain the title of its parent
 * or should be clearly marked as without parent.
 */
class ParentItem extends Column
{
    private array $items = [];
    private CollectionFactory $collectionFactory;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        CollectionFactory $collectionFactory,
        array $components = [],
        array $data = []
    ) {
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $name = $this->getData('name');
                if (!empty($item['parent_id'])) {
                    $item[$name] = $this->getMenuItemTitleById((int)$item['parent_id']);
                } else {
                    $item[$name] = __('(no parent)');
                }
            }
        }

        return $dataSource;
    }

    /**
     * Get the menu item title by it's Id.
     *
     * @param int $menuItemId
     * @return string
     */
    private function getMenuItemTitleById(int $menuItemId)
    {
        if (empty($this->items)) {
            $this->prepareItemsFromCollection();
        }

        // Return "no parent" for cases of menu items with provided menu_item_id
        // are missing. This is possible when somebody (accidentally) removed
        // the parent menu item.
        return $this->items[$menuItemId] ?? __('(no parent)');
    }

    /**
     * Fill in the {@see $this::$items} property from a collection.
     *
     * We need to get all data, because $dataSource contains items,
     * that visible on the current page.
     */
    private function prepareItemsFromCollection()
    {
        $collection = $this->collectionFactory->create();

        /** @var Item $menuItem */
        foreach ($collection as $menuItem) {
            $this->items[(int)$menuItem->getId()] = $menuItem->getTitle();
        }
    }
}
