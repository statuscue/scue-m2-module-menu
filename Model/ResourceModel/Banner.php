<?php

declare(strict_types=1);

namespace Scue\Menu\Model\ResourceModel;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Banner extends AbstractDb
{
    /**
     * @inheritDoc
     * @noinspection MagicMethodsValidityInspection
     */
    protected function _construct()
    {
        $this->_init('scue_menu_banners', 'banner_id');
    }

    /**
     * Disable all banners at once.
     *
     * @throws LocalizedException
     */
    public function disableAllBanners()
    {
        $connection = $this->getConnection();
        $connection->update($this->getMainTable(), ['is_active' => 0]);
    }
}
