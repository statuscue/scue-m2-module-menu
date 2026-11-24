<?php

declare(strict_types=1);

namespace Scue\Menu\Controller\Adminhtml\Banner;

use Scue\Menu\Model\BannerFactory;
use Scue\Menu\Model\ResourceModel\Banner as BannerResource;
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
    const ADMIN_RESOURCE = 'Scue_Menu::banner_delete';

    private BannerFactory $bannerFactory;
    private BannerResource $bannerResource;

    public function __construct(
        Context $context,
        BannerFactory $bannerFactory,
        BannerResource $bannerResource
    ) {
        $this->bannerFactory = $bannerFactory;
        $this->bannerResource = $bannerResource;
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
        $bannerId = $this->getRequest()->getParam('banner_id');
        if ($bannerId) {
            try {
                $model = $this->bannerFactory->create();
                $this->bannerResource->load($model, $bannerId);
                $this->bannerResource->delete($model);
                // display success message
                $this->messageManager->addSuccessMessage(__('You deleted the banner.'));
                // got to grid
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                // display error message
                $this->messageManager->addErrorMessage($e->getMessage());
                // go back to edit form
                return $resultRedirect->setPath('*/*/edit', ['banner_id' => $bannerId]);
            }
        }

        // display error message
        $this->messageManager->addErrorMessage(__('We can\'t find a banner to delete.'));

        // go to grid
        return $resultRedirect->setPath('*/*/');
    }
}
