<?php

declare(strict_types=1);

namespace Scue\Menu\Controller\Adminhtml\Item;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Catalog\Model\ImageUploader;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;

class Upload extends Action implements HttpPostActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Scue_Menu::save';

    /**
     * Image uploader instance
     *
     * Virtual type configured via {@see etc/di.xml}
     *
     * @var ImageUploader
     */
    private ImageUploader $itemBackgroundImageUploader;

    public function __construct(
        Context $context,
        ImageUploader $itemBackgroundImageUploader
    ) {
        parent::__construct($context);
        $this->itemBackgroundImageUploader = $itemBackgroundImageUploader;
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        try {
            $fileId = $this->getRequest()->getParam('param_name', 'menu_item_bg');
            $result = $this->itemBackgroundImageUploader->saveFileToTmpDir($fileId);
        } catch (\Exception $e) {
            $result = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
        }
        return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData($result);
    }
}
