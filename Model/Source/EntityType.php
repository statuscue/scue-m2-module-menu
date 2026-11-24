<?php

declare(strict_types=1);

namespace Scue\Menu\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

class EntityType implements OptionSourceInterface
{
    /**
     * @inheritDoc
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'category', 'label' => __('Category')],
            ['value' => 'page', 'label' => __('Page')],
            ['value' => 'product', 'label' => __('Product')],
            ['value' => 'custom', 'label' => __('Custom Link')],
        ];
    }
}
