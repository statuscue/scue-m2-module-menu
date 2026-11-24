<?php

declare(strict_types=1);

namespace Scue\Menu\Model\Entity;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Cms\Model\Page;
use Magento\Framework\UrlInterface as MagentoUrlBuilder;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

class UrlBuilder
{
    private CategoryRepositoryInterface $categoryRepository;
    private ProductRepositoryInterface $productRepository;
    private PageRepositoryInterface $pageRepository;
    private MagentoUrlBuilder $urlBuilder;
    private StoreManagerInterface $storeManager;
    private LoggerInterface $logger;

    public function __construct(
        CategoryRepositoryInterface $categoryRepository,
        ProductRepositoryInterface $productRepository,
        PageRepositoryInterface $pageRepository,
        MagentoUrlBuilder $urlBuilder,
        StoreManagerInterface $storeManager,
        LoggerInterface $logger
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->productRepository = $productRepository;
        $this->pageRepository = $pageRepository;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
        $this->urlBuilder = $urlBuilder;
    }

    public function getCustomUrl(string $relativeUrl): string
    {
        // Special case
        if ($relativeUrl === '#') {
            return $relativeUrl;
        }

        $relativeUrl = \ltrim($relativeUrl, '/');
        return $this->urlBuilder->getUrl(null, [
            '_direct' => $relativeUrl,
            '_use_rewrite' => true,
        ]);
    }

    public function getEntityUrl(?int $entityId, string $entityType): string
    {
        if ($entityId === null) {
            return '#';
        }

        $url = '#';
        try {
            $storeId = (int)$this->storeManager->getStore()->getId();
            switch ($entityType) {
                case 'category':
                    $url = $this->getCategoryUrlById($entityId, $storeId);
                    break;
                case 'page':
                    $url = $this->getPageUrlById($entityId);
                    break;
                case 'product':
                    $url = $this->getProductUrlById($entityId, $storeId);
                    break;
            }
        } catch (\Exception $e) {
            $this->logger->warning('Can not fetch related entity url', [
                'entityId' => $entityId,
                'entityType' => $entityType,
                'originalMessage' => $e->getMessage()
            ]);
        }

        return $url;
    }

    private function getCategoryUrlById(int $categoryId, ?int $storeId = null): string
    {
        /** @var Category $category */
        $category = $this->categoryRepository->get($categoryId, $storeId);
        return $category->getUrl();
    }

    private function getPageUrlById(int $pageId): string
    {
        /** @var Page $category */
        $page = $this->pageRepository->getById($pageId);
        /** @see \Magento\Cms\Helper\Page::getPageUrl */
        return $this->urlBuilder->getUrl(null, ['_direct' => $page->getIdentifier()]);
    }

    private function getProductUrlById(int $productId, ?int $storeId = null): string
    {
        /** @var Product $product */
        $product = $this->productRepository->getById($productId, false, $storeId);
        return $product->getProductUrl();
    }
}
