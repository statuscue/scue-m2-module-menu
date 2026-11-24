<?php

declare(strict_types=1);

namespace Scue\Menu\Ui\Component\Banner\Listing\Column;

use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class Banner extends Column
{
    private const ALT_FIELD = 'description';

    private UrlInterface $urlBuilder;
    private ImageHelper $imageHelper;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        ImageHelper $imageHelper,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->imageHelper = $imageHelper;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as &$item) {
                $url = $item['path'] ?: $this->imageHelper->getDefaultPlaceholderUrl('small_image');

                $item[$fieldName . '_src'] = $url;
                $item[$fieldName . '_orig_src'] = $url;
                $item[$fieldName . '_alt'] = $this->getAlt($item) ?: '';
                $item[$fieldName . '_link'] = $this->urlBuilder->getUrl(
                    'scue_menu/banner/edit',
                    ['banner_id' => (int)$item['banner_id']]
                );

                unset($url);
            }
            unset($item, $fieldName);
        }

        return $dataSource;
    }

    protected function getAlt(array $row): ?string
    {
        $altField = $this->getData('config/altField') ?: self::ALT_FIELD;
        return $row[$altField] ?? null;
    }
}
