<?php

declare(strict_types=1);

namespace Scue\Menu\Controller\Adminhtml\Item;

use Scue\Menu\Model\ItemFactory as MenuItemFactory;
use Scue\Menu\Model\ResourceModel\Item as MenuItemResource;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Backend\App\Action;
use Magento\Framework\View\Result\PageFactory;

class Edit extends Action implements HttpGetActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Scue_Menu::save';

    protected PageFactory $resultPageFactory;
    private MenuItemFactory $menuItemFactory;
    private MenuItemResource $menuItemResource;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        MenuItemFactory $menuItemFactory,
        MenuItemResource $menuItemResource
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->menuItemFactory = $menuItemFactory;
        $this->menuItemResource = $menuItemResource;
        parent::__construct($context);
    }

    /**
     * Init actions
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function initAction()
    {
        // load layout, set active menu and breadcrumbs
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Scue_Menu::items')
            ->addBreadcrumb(__('Menu Items'), __('Menu Items'))
            ->addBreadcrumb(__('Manage Menu Items'), __('Manage Menu Items'));
        return $resultPage;
    }

    /**
     * Edit menu item
     *
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Backend\Model\View\Result\Redirect
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('menu_item_id');
        $model = $this->menuItemFactory->create();

        if ($id) {
            $this->menuItemResource->load($model, $id);
            if (!$model->getId()) {
                $this->messageManager->addErrorMessage(__('This menu item no longer exists.'));
                /** \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
        }

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->initAction();
        $resultPage->addBreadcrumb(
            $id ? __('Edit Menu Item') : __('New Menu Item'),
            $id ? __('Edit Menu Item') : __('New Menu Item')
        );
        $resultPage->getConfig()->getTitle()->prepend(__('Menu Items'));
        $resultPage->getConfig()->getTitle()
            ->prepend($model->getId() ? $model->getTitle() : __('New Menu Item'));

        return $resultPage;
    }
}
