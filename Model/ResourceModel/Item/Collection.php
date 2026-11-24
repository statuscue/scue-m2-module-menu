<?php

declare(strict_types=1);

namespace Scue\Menu\Model\ResourceModel\Item;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * @inheritDoc
     * @noinspection MagicMethodsValidityInspection
     */
    protected function _construct()
    {
        $this->_init(
            \Scue\Menu\Model\Item::class,
            \Scue\Menu\Model\ResourceModel\Item::class
        );
    }
}
