<?php

declare(strict_types=1);

namespace Scue\Menu\Controller\Adminhtml\Banner;

use Scue\Menu\Model\BannerFactory;
use Scue\Menu\Model\ResourceModel\Banner as BannerResource;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Action\HttpPostActionInterface;

class Disable extends Action implements HttpPostActionInterface
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
                $banner = $this->bannerFactory->create();
                $this->bannerResource->load($banner, $bannerId);

                // disable banner
                $banner->disable();
                // and don't forget to save it to the database!
                $this->bannerResource->save($banner);
                $this->messageManager->addSuccessMessage(__('You disabled a banner with ID #%1.', $bannerId));

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
        $this->messageManager->addErrorMessage(__('We can\'t find a banner to disable.'));

        // go to grid
        return $resultRedirect->setPath('*/*/');
    }
}
