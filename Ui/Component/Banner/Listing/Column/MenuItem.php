<?php

declare(strict_types=1);

namespace Scue\Menu\Ui\Component\Banner\Listing\Column;

use Scue\Menu\Model\Item;
use Scue\Menu\Model\ResourceModel\Item\CollectionFactory as MenuItemCollectionFactory;
use Magento\Framework\UrlInterface as UrlBuilder;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Column "menu_item" in the admin grid with a list of banners.
 *
 * Note, that this column should contain the title of a menu item and a link to the edit page.
 *
 * Or should be clearly marked, that banner is not attached to any of menu items.
 */
class MenuItem extends Column
{
    private UrlBuilder $urlBuilder;
    private MenuItemCollectionFactory $collectionFactory;
    private array $items = [];

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlBuilder $urlBuilder,
        MenuItemCollectionFactory $collectionFactory,
        array $components = [],
        array $data = []
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $name = $this->getData('name');
                if (!empty($item['menu_item_id'])) {
                    $item[$name] = $this->getMenuItemTitleById((int)$item['menu_item_id']);
                    $item['menu_item_link'] = $this->urlBuilder->getUrl('scue_menu/item/edit', [
                        'menu_item_id' => $item['menu_item_id']
                    ]);
                } else {
                    $item[$name] = __('-- not attached --');
                    $item['menu_item_link'] = null;
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

        return $this->items[$menuItemId] ?? __('-- no title --');
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
