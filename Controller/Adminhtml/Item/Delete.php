<?php

declare(strict_types=1);

namespace Scue\Menu\Controller\Adminhtml\Item;

use Scue\Menu\Model\ItemFactory as MenuItemFactory;
use Scue\Menu\Model\ResourceModel\Item as MenuItemResource;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;

class Delete extends Action implements HttpPostActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Scue_Menu::delete';

    private MenuItemFactory $menuItemFactory;
    private MenuItemResource $menuItemResource;

    public function __construct(
        Context $context,
        MenuItemFactory $menuItemFactory,
        MenuItemResource $menuItemResource
    ) {
        $this->menuItemFactory = $menuItemFactory;
        $this->menuItemResource = $menuItemResource;
        parent::__construct($context);
    }

    /**
     * Delete action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        // check if we know what should be deleted
        $menuItemId = $this->getRequest()->getParam('menu_item_id');
        if ($menuItemId) {
            try {
                $model = $this->menuItemFactory->create();
                $this->menuItemResource->load($model, $menuItemId);
                $this->menuItemResource->delete($model);
                // display success message
                $this->messageManager->addSuccessMessage(__('You deleted the menu item.'));
                // got to grid
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                // display error message
                $this->messageManager->addErrorMessage($e->getMessage());
                // go back to edit form
                return $resultRedirect->setPath('*/*/edit', ['menu_item_id' => $menuItemId]);
            }
        }

        // display error message
        $this->messageManager->addErrorMessage(__('We can\'t find a menu item to delete.'));

        // go to grid
        return $resultRedirect->setPath('*/*/');
    }
}
