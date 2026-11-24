<?php

declare(strict_types=1);

namespace Scue\Menu\Ui\Component\Banner\Listing\Column;

use Magento\Ui\Component\Listing\Columns\Column;

class Status extends Column
{
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as &$item) {
                $item[$fieldName] = (int)$item['is_active'] === 1 ? __('Enabled') : __('Disabled');
            }
            unset($item, $fieldName);
        }

        return $dataSource;
    }
}
