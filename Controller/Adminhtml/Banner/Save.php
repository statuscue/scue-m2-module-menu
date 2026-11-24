<?php

declare(strict_types=1);

namespace Scue\Menu\Controller\Adminhtml\Banner;

use Scue\Menu\Model\Banner;
use Scue\Menu\Model\BannerFactory;
use Scue\Menu\Model\Entity\HandlerFactory as EntityHandlerFactory;
use Scue\Menu\Model\ResourceModel\Banner as BannerResource;
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
    const ADMIN_RESOURCE = 'Scue_Menu::banner_save';

    private DataPersistorInterface $dataPersistor;
    private BannerFactory $bannerFactory;
    private BannerResource $bannerResource;
    private ImageUploader $imageUploader;
    private StoreManagerInterface $storeManager;
    private EntityHandlerFactory $entityFactory;

    public function __construct(
        Context $context,
        DataPersistorInterface $dataPersistor,
        ImageUploader $imageUploader,
        StoreManagerInterface $storeManager,
        BannerFactory $bannerFactory,
        BannerResource $bannerResource,
        EntityHandlerFactory $entityFactory
    ) {
        $this->dataPersistor = $dataPersistor;
        $this->imageUploader = $imageUploader;
        $this->storeManager = $storeManager;
        $this->bannerFactory = $bannerFactory;
        $this->bannerResource = $bannerResource;
        $this->entityFactory = $entityFactory;
        parent::__construct($context);
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

        /** @var Banner $banner */
        $banner = $this->bannerFactory->create();
        $bannerId = (int)$this->getRequest()->getParam('banner_id');
        if ($bannerId) {
            $this->bannerResource->load($banner, $bannerId);
            if (!$banner->getId()) {
                $this->messageManager->addErrorMessage(__('This banner no longer exists.'));
                return $resultRedirect->setPath('*/*/');
            }
        }

        if (empty($data['banner_id'])) {
            $data['banner_id'] = null;
        }

        if ($data['menu_item_id'] !== null && !\is_numeric($data['menu_item_id'])) {
            $this->messageManager->addErrorMessage(
                __('Please, choose the correct menu item in field "Attach to Menu Item".')
            );
            $this->dataPersistor->set('scue_menu_banner', $data);
            return $resultRedirect->setPath('*/*/edit', ['banner_id' => $bannerId]);
        }

        if (isset($data['banner'][0]['name'], $data['banner'][0]['tmp_name'])) {
            // file was uploaded via "Upload" controller,
            // so we need to move it from tmp folder
            // @see \Magento\Catalog\Model\Category\Attribute\Backend\Image::beforeSave
            /** @var StoreInterface $store */
            $store = $this->storeManager->getStore();
            $baseMediaDir = $store->getBaseMediaDir();
            $relativePath = $this->imageUploader->moveFileFromTmp($data['banner'][0]['name'], true);
            $data['path'] = DIRECTORY_SEPARATOR . $baseMediaDir . DIRECTORY_SEPARATOR . $relativePath;
        } elseif (isset($data['banner'][0]['url']) && !isset($data['banner'][0]['tmp_name'])) {
            // file was previously uploaded to "Media Gallery"
            $data['path'] = $data['banner'][0]['url'];
        } else {
            $data['path'] = '';
        }

        // Process "link" field.
        // Required fields are: "entity_type", "related_entity_id" and "custom_link".
        $link = $data['link'] ?? [];

        // Note! You need to pass an array with a fixed key to pass correct args to the object's constructor
        // via factory creation procedure.
        $entity = $this->entityFactory->create(['entity' => $link]);
        $entityType = $entity->getEntityType();
        $data['entity_type'] = $entityType === 'default' ? 'custom' : $entityType;
        $data['related_entity_id'] = $entity->getRelatedEntityIdByType($entityType);
        $data['custom_link'] = $entity->getCustomLink();

        $banner->setData($data);

        try {
            $this->bannerResource->save($banner);
            $this->messageManager->addSuccessMessage(__('You saved the banner.'));
            return $this->processResultRedirect($banner, $resultRedirect, $data);
        } catch (LocalizedException $e) {
            $this->messageManager->addExceptionMessage($e->getPrevious() ?: $e);
        } catch (\Throwable $e) {
            $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the banner.'));
        }

        $this->dataPersistor->set('scue_menu_banner', $data);
        return $resultRedirect->setPath('*/*/edit', ['banner_id' => $bannerId]);
    }

    /**
     * Process result redirect
     *
     * @param Banner $model
     * @param \Magento\Backend\Model\View\Result\Redirect $resultRedirect
     * @param array $data
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws LocalizedException
     */
    private function processResultRedirect($model, $resultRedirect, $data)
    {
        $this->dataPersistor->clear('scue_menu_banner');
        if ($this->getRequest()->getParam('back')) {
            return $resultRedirect->setPath('*/*/edit', ['banner_id' => $model->getId(), '_current' => true]);
        }
        return $resultRedirect->setPath('*/*/');
    }
}
