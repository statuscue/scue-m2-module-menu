<?php

declare(strict_types=1);

namespace Scue\Menu\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Item extends AbstractDb
{
    /**
     * @inheritDoc
     * @noinspection MagicMethodsValidityInspection
     */
    protected function _construct()
    {
        $this->_init('scue_menu_items', 'menu_item_id');
    }
}
