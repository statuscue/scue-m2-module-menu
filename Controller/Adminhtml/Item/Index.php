<?php

declare(strict_types=1);

namespace Scue\Menu\Controller\Adminhtml\Item;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends \Magento\Backend\App\Action
{
    public const ADMIN_RESOURCE = 'Scue_Menu::items';

    private PageFactory $resultPageFactory;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Scue_Menu::items');
        $resultPage->addBreadcrumb(__('Menu Items'), __('Menu Items'));
        $resultPage->addBreadcrumb(__('Manage Menu Items'), __('Manage Menu Items'));
        $resultPage->getConfig()->getTitle()->prepend(__('Menu Items'));
        return $resultPage;
    }
}
