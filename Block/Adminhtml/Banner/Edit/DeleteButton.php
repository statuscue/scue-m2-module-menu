<?php

declare(strict_types=1);

namespace Scue\Menu\Block\Adminhtml\Banner\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class DeleteButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * @inheritDoc
     */
    public function getButtonData(): array
    {
        $data = [];
        if ($this->getBannerId()) {
            $data = [
                'label' => __('Delete Banner'),
                'class' => 'delete',
                'sort_order' => 20,
                'on_click' => sprintf(
                    'deleteConfirm("%1$s", "%2$s", {"data": {}})',
                    __('Are you sure you want to do this?'),
                    $this->getDeleteUrl()
                ),
            ];
        }
        return $data;
    }

    /**
     * Url to send delete requests to.
     *
     * @return string
     */
    public function getDeleteUrl(): string
    {
        return $this->getUrl('*/*/delete', ['banner_id' => $this->getBannerId()]);
    }
}
