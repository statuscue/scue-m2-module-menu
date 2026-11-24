<?php

declare(strict_types=1);

namespace Scue\Menu\Controller\Adminhtml\Item;

use Scue\Menu\Model\Entity\HandlerFactory as EntityHandlerFactory;
use Scue\Menu\Model\Item;
use Scue\Menu\Model\ItemFactory as MenuItemFactory;
use Scue\Menu\Model\ResourceModel\Item as MenuItemResource;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Catalog\Model\ImageUploader;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Save menu item action.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Save extends Action implements HttpPostActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Scue_Menu::save';

    private DataPersistorInterface $dataPersistor;
    private EntityHandlerFactory $entityFactory;
    private MenuItemFactory $menuItemFactory;
    private MenuItemResource $menuItemResource;
    private StoreManagerInterface $storeManager;
    private ImageUploader $itemBackgroundImageUploader;

    public function __construct(
        Context $context,
        DataPersistorInterface $dataPersistor,
        EntityHandlerFactory $entityFactory,
        MenuItemFactory $menuItemFactory,
        MenuItemResource $menuItemResource,
        StoreManagerInterface $storeManager,
        ImageUploader $itemBackgroundImageUploader
    ) {
        parent::__construct($context);
        $this->dataPersistor = $dataPersistor;
        $this->entityFactory = $entityFactory;
        $this->menuItemFactory = $menuItemFactory;
        $this->menuItemResource = $menuItemResource;
        $this->storeManager = $storeManager;
        $this->itemBackgroundImageUploader = $itemBackgroundImageUploader;
    }

    /**
     * Save action
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        $data = $this->getRequest()->getPostValue();
        if (empty($data)) {
            return $resultRedirect->setPath('*/*/');
        }

        /** @var \Scue\Menu\Model\Item $menuItem */
        $menuItem = $this->menuItemFactory->create();
        $menuItemId = (int)$this->getRequest()->getParam('menu_item_id');
        if ($menuItemId) {
            $this->menuItemResource->load($menuItem, $menuItemId);
            if (!$menuItem->getId()) {
                $this->messageManager->addErrorMessage(__('This menu item no longer exists.'));
                return $resultRedirect->setPath('*/*/');
            }
        }

        if (empty($data['menu_item_id'])) {
            $data['menu_item_id'] = null;
        }

        if (empty($data['parent_id'])) {
            $data['parent_id'] = Item::NO_PARENT_ID;
        } elseif ($data['parent_id'] !== null && !\is_numeric($data['parent_id'])) {
            $this->messageManager->addErrorMessage(
                __('Please, choose the correct menu item in field "Parent Menu Item".')
            );
            $this->dataPersistor->set('scue_menu_item', $data);
            return $resultRedirect->setPath('*/*/edit', ['menu_item_id' => $menuItemId]);
        }

        // Don't allow to choose the current menu item as its own parent.
        if ($data['menu_item_id'] !== null && (int)$data['menu_item_id'] === (int)$data['parent_id']) {
            $this->messageManager->addErrorMessage(__('Current menu item can\'t be its own parent.'));
            $this->dataPersistor->set('scue_menu_item', $data);
            return $resultRedirect->setPath('*/*/edit', ['menu_item_id' => $menuItemId]);
        }

        // Process "entity" field.
        // If field is missing or empty - save menu item with a "custom" type.
        $entity = $data['entity'] ?? [];
        $entityHandler = $this->entityFactory->create(['entity' => $entity]);
        $entityType = $entityHandler->getEntityType();
        // uiSelect component uses "default" key for custom urls.
        // For our convenience I will store it with a "custom" key.
        // The real entity type comes in handy a little bit further,
        // because the value can be found by the name of the key,
        // which has the same name as its type, e.g. $data[entity][type => category, category => {value}]
        $data['entity_type'] = $entityType === 'default' ? 'custom' : $entityType;
        $data['related_entity_id'] = $entityHandler->getRelatedEntityIdByType($entityType);
        $data['custom_link'] = $entityHandler->getCustomLink();

        // Process "bg_color": set to null if not specified
        if (empty($data['bg_color'])) {
            $data['bg_color'] = null;
        }

        // Process "bg_image"
        if (isset($data['menu_item_bg'][0]['name'], $data['menu_item_bg'][0]['tmp_name'])) {
            // file was uploaded via "Upload" controller,
            // so we need to move it from tmp folder
            // @see \Magento\Catalog\Model\Category\Attribute\Backend\Image::beforeSave
            /** @var StoreInterface $store */
            $store = $this->storeManager->getStore();
            $baseMediaDir = $store->getBaseMediaDir();
            $relativePath = $this->itemBackgroundImageUploader->moveFileFromTmp($data['menu_item_bg'][0]['name'], true);
            $data['bg_image'] = DIRECTORY_SEPARATOR . $baseMediaDir . DIRECTORY_SEPARATOR . $relativePath;
        } elseif (isset($data['menu_item_bg'][0]['url']) && !isset($data['menu_item_bg'][0]['tmp_name'])) {
            // file was previously uploaded to "Media Gallery"
            $data['bg_image'] = $data['menu_item_bg'][0]['url'];
        } else {
            $data['bg_image'] = null;
        }

        $menuItem->setData($data);

        try {
            $this->menuItemResource->save($menuItem);
            $this->messageManager->addSuccessMessage(__('You saved the menu item.'));
            return $this->processResultRedirect($menuItem, $resultRedirect, $data);
        } catch (LocalizedException $e) {
            $this->messageManager->addExceptionMessage($e->getPrevious() ?: $e);
        } catch (\Throwable $e) {
            $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the menu item.'));
        }

        $this->dataPersistor->set('scue_menu_item', $data);
        return $resultRedirect->setPath('*/*/edit', ['menu_item_id' => $menuItemId]);
    }

    private function processResultRedirect($model, $resultRedirect, $data)
    {
        if ($this->getRequest()->getParam('back', false) === 'duplicate') {
            // append (copy) to menu item title
            $title = $data['title'] . ' ' . __('(copy)');

            $newMenuItem = $this->menuItemFactory->create(['data' => $data]);
            $newMenuItem->setId(null);
            $newMenuItem->setTitle($title);

            $this->menuItemResource->save($newMenuItem);

            $this->messageManager->addSuccessMessage(__('You duplicated the menu item.'));
            return $resultRedirect->setPath(
                '*/*/edit',
                ['menu_item_id' => $newMenuItem->getId(), '_current' => true]
            );
        }

        $this->dataPersistor->clear('scue_menu_item');
        if ($this->getRequest()->getParam('back')) {
            return $resultRedirect->setPath(
                '*/*/edit',
                ['menu_item_id' => $model->getId(), '_current' => true]
            );
        }
        return $resultRedirect->setPath('*/*/');
    }
}
