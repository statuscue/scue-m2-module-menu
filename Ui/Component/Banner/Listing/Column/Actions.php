<?php

declare(strict_types=1);

namespace Scue\Menu\Ui\Component\Banner\Listing\Column;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class Actions extends Column
{
    public const URL_PATH_EDIT = 'scue_menu/banner/edit';
    public const URL_PATH_DELETE = 'scue_menu/banner/delete';
    public const URL_PATH_ENABLE = 'scue_menu/banner/enable';
    public const URL_PATH_DISABLE = 'scue_menu/banner/disable';

    private UrlInterface $urlBuilder;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * @inheritDoc
     */
    public function prepareDataSource(array $dataSource): array
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as &$item) {
                if (isset($item['banner_id'])) {
                    $bannerId = (int)$item['banner_id'];
                    $status = (int)$item['is_active'];

                    $item[$fieldName]['edit'] = [
                        'href' => $this->urlBuilder->getUrl(self::URL_PATH_EDIT, ['banner_id' => $item['banner_id']]),
                        'label' => __('Edit'),
                    ];

                    $item[$fieldName]['delete'] = [
                        'post' => true,
                        'label' => __('Delete'),
                        'href' => $this->urlBuilder->getUrl(self::URL_PATH_DELETE, ['banner_id' => $item['banner_id']]),
                        'confirm' => [
                            'title' => __('Delete banner #%1', $bannerId),
                            'message' => __('Are you sure you want to delete a #%1 record?', $bannerId),
                        ],
                    ];

                    if ($status === 0) {
                        $item[$fieldName]['enable'] = [
                            'post' => true,
                            'label' => __('Enable'),
                            'href' => $this->urlBuilder->getUrl(
                                self::URL_PATH_ENABLE,
                                ['banner_id' => $item['banner_id']]
                            ),
                            'confirm' => [
                                'title' => __('Enable a banner with ID #%1?', $bannerId),
                                'message' => __('Only one banner may be enabled at the same moment. All other banners will be disabled.'),
                            ],
                        ];
                    } else {
                        $item[$fieldName]['disable'] = [
                            'post' => true,
                            'label' => __('Disable'),
                            'href' => $this->urlBuilder->getUrl(
                                self::URL_PATH_DISABLE,
                                ['banner_id' => $item['banner_id']]
                            ),
                            'confirm' => [
                                'title' => __('Disable a banner with ID #%1?', $bannerId),
                                'message' => __('Are you sure you want to disable a banner?'),
                            ],
                        ];
                    }
                }
            }
        }

        return $dataSource;
    }
}
