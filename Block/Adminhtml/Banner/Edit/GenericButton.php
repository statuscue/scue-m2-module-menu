<?php

declare(strict_types=1);

namespace Scue\Menu\Block\Adminhtml\Banner\Edit;

use Magento\Backend\Block\Widget\Context;

class GenericButton
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * @param Context $context
     */
    public function __construct(Context $context)
    {
        $this->context = $context;
    }

    /**
     * Return menu item ID
     *
     * @return int
     */
    public function getBannerId(): int
    {
        return (int) $this->context->getRequest()->getParam('banner_id');
    }

    /**
     * Generate url by route and parameters
     *
     * @param string $route
     * @param array $params
     * @return string
     */
    public function getUrl(string $route = '', array $params = []): string
    {
        return $this->context->getUrlBuilder()->getUrl($route, $params);
    }
}
