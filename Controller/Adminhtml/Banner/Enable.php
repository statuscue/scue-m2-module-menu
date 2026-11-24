<?php

declare(strict_types=1);

namespace Scue\Menu\Controller\Adminhtml\Banner;

use Scue\Menu\Model\BannerFactory;
use Scue\Menu\Model\ResourceModel\Banner as BannerResource;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Action\HttpPostActionInterface;

class Enable extends Action implements HttpPostActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Scue_Menu::banner_save';

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

    public function execute()
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        $bannerId = $this->getRequest()->getParam('banner_id');
        if ($bannerId) {
            try {
                // At the moment may be only one active banner.
                // That's why we must disable all banners and then re-enable selected.
                $this->bannerResource->disableAllBanners();

                // Re-enable the selected banner.
                $banner = $this->bannerFactory->create();
                $this->bannerResource->load($banner, $bannerId);

                // Enable and save to the database.
                $banner->enable();
                $this->bannerResource->save($banner);
                $this->messageManager->addSuccessMessage(__('You enabled a banner with ID #%1.', $bannerId));

                // go to grid
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                // display error message
                $this->messageManager->addErrorMessage($e->getMessage());
                // go back to edit form
                return $resultRedirect->setPath('*/*/edit', ['banner_id' => $bannerId]);
            }
        }

        // display error message
        $this->messageManager->addErrorMessage(__('We can\'t find a banner to enable.'));

        // go to grid
        return $resultRedirect->setPath('*/*/');
    }
}
