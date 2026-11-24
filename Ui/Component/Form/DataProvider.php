<?php

declare(strict_types=1);

namespace Scue\Menu\Ui\Component\Form;

use Scue\Menu\Api\Data\ItemInterface;
use Scue\Menu\Model\Item;
use Scue\Menu\Model\ItemFactory as MenuItemFactory;
use Scue\Menu\Model\ResourceModel\Item\Collection;
use Scue\Menu\Model\ResourceModel\Item\CollectionFactory as MenuItemCollectionFactory;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Ui\DataProvider\Modifier\PoolInterface;
use Magento\Ui\DataProvider\ModifierPoolDataProvider;
use function basename;

/**
 * Class DataProvider
 */
class DataProvider extends ModifierPoolDataProvider
{
    /**
     * @var Collection
     */
    protected $collection;

    /**
     * @var array
     */
    protected $loadedData;

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    private RequestInterface $request;
    private MenuItemFactory $menuItemFactory;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        RequestInterface $request,
        DataPersistorInterface $dataPersistor,
        MenuItemFactory $menuItemFactory,
        MenuItemCollectionFactory $collectionFactory,
        array $meta = [],
        array $data = [],
        PoolInterface $pool = null
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data, $pool);
        $this->collection = $collectionFactory->create();
        $this->request = $request;
        $this->dataPersistor = $dataPersistor;
        $this->menuItemFactory = $menuItemFactory;
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }

        /** @var Item $menuItem */
        $menuItem = $this->getMenuItem();
        $this->loadedData[(int)$menuItem->getId()] = $menuItem->getData();

        return $this->loadedData;
    }

    private function getMenuItem(): ItemInterface
    {
        // Empty object with data from dataPersistor class.
        // Applicable, for example, on a new/edit page, which we can't save due to some errors.
        $data = $this->dataPersistor->get('scue_menu_item');
        if (!empty($data)) {
            $this->dataPersistor->clear('scue_menu_item');
            return $this->menuItemFactory->create()->setData($data);
        }

        // Load data from database for existing resource.
        // E.g. on "edit" page.
        $menuItemId = $this->getMenuItemId();
        if ($menuItemId) {
            /** @var Item $menuItem */
            $menuItem = $this->collection->getFirstItem();

            // Save "entity" as model data.
            // Not a part of menuItem model, but should be present on "edit" page.
            $menuItem->setData('entity', $this->getDataForUrlInput($menuItem));
            // Add "menu_item_bg" to DataObject
            $menuItem->setData('menu_item_bg', $this->getDataForItemBackgroundImageUploader($menuItem));

            return $menuItem;
        }

        // No one fires... return empty model, e.g. on a "new" page.
        return $this->menuItemFactory->create();
    }

    private function getMenuItemId(): int
    {
        return (int)$this->request->getParam($this->getRequestFieldName());
    }

    /**
     * Get data for "urlInput" component in an appropriate format.
     *
     * @param Item $menuItem
     * @return array
     */
    private function getDataForUrlInput(Item $menuItem): array
    {
        $entityType = $menuItem->getEntityType();
        $entity = [
            'setting' => false,
            'type' => $entityType,
            'default' => '',
        ];

        if ($entityType === 'custom') {
            // Use the component's type value "default", not "custom'
            $entity['type'] = 'default';
            $entity['default'] = $menuItem->getCustomLink();
        } else {
            $entity[$entityType] = (string)$menuItem->getRelatedEntityId();
        }

        return $entity;
    }

    /**
     * Get data for "ImageUploader" component in an appropriate format.
     *
     * @param ItemInterface $menuItem
     * @return array
     */
    private function getDataForItemBackgroundImageUploader(ItemInterface $menuItem): array
    {
        $path = $menuItem->getBgImage();
        $image = [];
        if (!empty($path)) {
            $image[0] = [
                'previewType' => 'image',
                'url' => $path,
                'name' => basename($path),
                'type' => '',
                'size' => '',
                'id' => '',
            ];
        }

        return $image;
    }
}
