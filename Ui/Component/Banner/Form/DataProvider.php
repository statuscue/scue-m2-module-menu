<?php

declare(strict_types=1);

namespace Scue\Menu\Ui\Component\Banner\Form;

use Scue\Menu\Api\Data\BannerInterface;
use Scue\Menu\Model\Banner;
use Scue\Menu\Model\BannerFactory;
use Scue\Menu\Model\ResourceModel\Banner\CollectionFactory as BannerCollectionFactory;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Ui\DataProvider\Modifier\PoolInterface;
use Magento\Ui\DataProvider\ModifierPoolDataProvider;

/**
 * Class DataProvider
 */
class DataProvider extends ModifierPoolDataProvider
{
    /**
     * @var \Scue\Menu\Model\ResourceModel\Banner\Collection
     */
    protected $collection;

    /**
     * @var array
     */
    protected $loadedData;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    private RequestInterface $request;
    private BannerFactory $bannerFactory;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        RequestInterface $request,
        DataPersistorInterface $dataPersistor,
        BannerFactory $bannerFactory,
        BannerCollectionFactory $bannerCollectionFactory,
        array $meta = [],
        array $data = [],
        PoolInterface $pool = null
    ) {
        $this->collection = $bannerCollectionFactory->create();
        $this->request = $request;
        $this->dataPersistor = $dataPersistor;
        $this->bannerFactory = $bannerFactory;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data, $pool);
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

        /** @var Banner $banner */
        $banner = $this->getBanner();
        $this->loadedData[$banner->getId()] = $banner->getData();

        return $this->loadedData;
    }

    private function getBanner(): BannerInterface
    {
        // Empty object with data from dataPersistor class.
        // Applicable, for example, on a new/edit page, which we can't save due to some errors.
        $data = $this->dataPersistor->get('scue_menu_banner');
        if (!empty($data)) {
            $this->dataPersistor->clear('scue_menu_banner');
            return $this->bannerFactory->create()->setData($data);
        }

        // Load data from database if banner id exists, e.g. on "edit" page.
        $bannerId = $this->getBannerId();
        if ($bannerId) {
            /** @var Banner $banner */
            $banner = $this->collection->getFirstItem();

            // Add "link" to model's DataObject
            $banner->setData('link', $this->getDataForUrlInput($banner));
            // Add "banner" to model's DataObject
            $banner->setData('banner', $this->getDataForImageUploader($banner));

            return $banner;
        }

        // Empty object. Applicable, for example, on a "new" page.
        return $this->bannerFactory->create();
    }

    private function getBannerId(): int
    {
        return (int) $this->request->getParam($this->getRequestFieldName());
    }

    /**
     * Get data for "urlInput" component in an appropriate format.
     *
     * @param Banner $banner
     * @return array
     */
    private function getDataForUrlInput(Banner $banner): array
    {
        $entityType = $banner->getEntityType();
        $entity = [
            'setting' => false,
            'type' => $entityType,
            'default' => '',
        ];

        if ($entityType === 'custom') {
            // Use the component's type value "default", not "custom'
            $entity['type'] = 'default';
            $entity['default'] = $banner->getCustomLink();
        } else {
            $entity[$entityType] = (string)$banner->getRelatedEntityId();
        }

        return $entity;
    }

    /**
     * Get data for "ImageUploader" component in an appropriate format.
     *
     * @param BannerInterface $banner
     * @return array
     */
    private function getDataForImageUploader(BannerInterface $banner): array
    {
        $path = $banner->getPath();
        $image = [];
        if (!empty($path)) {
            $image[0] = [
                'previewType' => 'image',
                'url' => $path,
                'name' => \basename($path),
                'type' => '',
                'size' => '',
                'id' => '',
            ];
        }

        return $image;
    }
}
