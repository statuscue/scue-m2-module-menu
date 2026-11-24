<?php

declare(strict_types=1);

namespace Scue\Menu\Block;

use Scue\Menu\Api\BannerRepositoryInterface;
use Scue\Menu\Api\Data\EntityInterface;
use Scue\Menu\Api\Data\ItemInterface;
use Scue\Menu\Model\Entity\UrlBuilder as EntityUrlBuilder;
use Scue\Menu\Model\Item\Tree\Builder;
use Scue\Menu\Model\Item\Tree\BuilderFactory as MenuItemTreeBuilderFactory;
use Magento\Catalog\Model\Layer\Resolver as LayerResolver;
use Magento\Catalog\Model\Product;
use Magento\Cms\Model\Page;
use Magento\Framework\Data\Tree\Node;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class Topmenu extends Template
{
    /**
     * Nesting limit.
     *
     * @see \Scue\Menu\Model\Item\Tree\Builder::buildTree
     *
     * @var int
     */
    private const NESTING_LIMIT = 4;

    private Escaper $escaper;
    private BannerRepositoryInterface $bannerRepository;
    private LayerResolver $layerResolver;
    private Page $currentPage;
    private Product $currentProduct;
    private EntityUrlBuilder $entityUrlBuilder;
    private MenuItemTreeBuilderFactory $treeBuilderFactory;
    private Node $tree;
    private array $menu = [];
    private array $urls = [];
    private bool $isCategory = false;
    private bool $isPage = false;
    private bool $isProduct = false;
    private string $currentUrl = '';
    private int $currentCategoryId;
    private int $currentPageId;
    private int $currentProductId;

    public function __construct(
        Context $context,
        Escaper $escaper,
        BannerRepositoryInterface $bannerRepository,
        LayerResolver $layerResolver,
        Page $currentPage,
        Product $currentProduct,
        EntityUrlBuilder $entityUrlBuilder,
        MenuItemTreeBuilderFactory $treeBuilder,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->entityUrlBuilder = $entityUrlBuilder;
        $this->treeBuilderFactory = $treeBuilder;
        $this->escaper = $escaper;
        $this->bannerRepository = $bannerRepository;
        $this->layerResolver = $layerResolver;
        $this->currentPage = $currentPage;
        $this->currentProduct = $currentProduct;
    }

    public function getMenu(): array
    {
        if (empty($this->menu)) {
            $this->setConditionalTags();

            $children = $this->getTree()->getAllChildNodes();
            $active = $this->findActiveMenuItem($children);
            $this->maybeActivateBranch($active);

            $this->menu = $this->buildMenu($this->getTree());
        }
        return $this->menu;
    }

    public function getEscaper(): Escaper
    {
        return $this->escaper;
    }

    private function setConditionalTags(): void
    {
        $fullActionName = $this->getRequest()->getFullActionName();
        if ($fullActionName === 'catalog_category_view') {
            $this->isCategory = true;
        } elseif ($fullActionName === 'catalog_product_view') {
            $this->isProduct = true;
        } elseif (\in_array($fullActionName, ['cms_index_index', 'cms_page_view'])) {
            $this->isPage = true;
        }
    }

    private function getTree(): Node
    {
        if (!isset($this->tree)) {
            $treeBuilder = $this->treeBuilderFactory->create();
            $this->tree = $treeBuilder->build();
            $this->rearrangeByLevel($this->tree);
        }
        return $this->tree;
    }

    /**
     * Move all child nodes, that exceed the allowed limit,
     * to the latest parent node, that is in the allowed limit.
     *
     * @param Node $parentNode
     */
    private function rearrangeByLevel(Node $parentNode): void
    {
        /** @var Node $childNode */
        foreach ($parentNode->getChildren() as $childNode) {
            $level = $childNode->getData('level');
            if ($level >= self::NESTING_LIMIT) {
                // In case when menu item exceed the nesting limit
                // It should be added as a child element of menu item with `$level-2`.
                // Another world as a child node of its parent's parent.
                $parentNode->getParent()->addChild($childNode);

                // The same for all other nesting menu items
                $children = $childNode->getAllChildNodes();
                foreach ($children as $child) {
                    $parentNode->getParent()->addChild($child);
                }
                break;
            }

            if ($childNode->hasChildren()) {
                $this->rearrangeByLevel($childNode);
            }
        }
    }

    private function findActiveMenuItem(array $nodes): ?Node
    {
        $activeMenuItem = null;
        /** @var Node $node */
        foreach ($nodes as $node) {
            $isActive = false;
            $entityType = $node->getData(EntityInterface::ENTITY_TYPE);
            $relatedEntityId = $node->getData(EntityInterface::RELATED_ENTITY_ID);
            $customLink = $node->getData(EntityInterface::CUSTOM_LINK);

            // At the moment build urls only for menu items with "custom" type
            if ($entityType === 'custom' && $customLink !== null) {
                $url = $this->getMenuItemUrl($node);
                $isActive = $url === $this->getCurrentUrl();
            } elseif ($relatedEntityId !== null) {
                if ($this->isCategory) {
                    $isActive = (int)$relatedEntityId === $this->getCurrentCategoryId();
                } elseif ($this->isProduct) {
                    $isActive = (int)$relatedEntityId === $this->getCurrentProductId();
                } elseif ($this->isPage) {
                    $isActive = (int)$relatedEntityId === $this->getCurrentPageId();
                }
            }

            if ($isActive && $activeMenuItem === null) {
                $activeMenuItem = $node;
                $node->setData('is_active', true);
            } else {
                $node->setData('is_active', false);
            }

            unset($entityType, $relatedEntityId, $customLink);
        }

        return $activeMenuItem;
    }

    private function maybeActivateBranch(?Node $node): void
    {
        if ($node === null) {
            return;
        }

        /** @var Node $item */
        foreach ($node->getPath() as $item) {
            $item->setData('is_active', true);
        }
    }

    private function buildMenu(Node $parentNode): array
    {
        $menu = [];
        /** @var Node $child */
        foreach ($parentNode->getChildren() as $child) {
            $menuItemId = $child->getId();

            $item = [
                'id' => $menuItemId,
                'url' => $this->getMenuItemUrl($child),
                'title' => $child->getData(ItemInterface::TITLE),
                'entity_type' => $child->getData(EntityInterface::ENTITY_TYPE),
                'is_active' => $child->getIsActive(),
                'has_children' => $child->hasChildren(),
                'children' => [],
            ];

            if ($child->hasChildren()) {
                $item['children'] = $this->buildMenu($child);
            }

            // Tabs should be treated differently
            if ($child->getData('level') === Builder::LEVEL_TAB) {
                // Add banner data for tabs (for desktop only)
                $item['banner'] = $this->getBanner($child);
                // Set a special flag, because the tab content must be displayed differently depending on the
                // presence or absence of the banner. This is controlled by a special class `.has-banner`
                // @see \Scue\Menu\Block\Topmenu::getCssClass()
                $child->setData('has_banner', $item['banner']['has_banner']);

                // Add an extra data for mobile menu.
                // Tabs (the second level) may have a specific background image and/or background color by design.
                $item['bg_color'] = $child->getData(ItemInterface::BG_COLOR);
                $item['bg_image'] = $child->getData(ItemInterface::BG_IMAGE);
            }

            // getCssClass call should be the latest
            $item['css_class'] = $this->getCssClass($child);

            $menu[$menuItemId] = $item;
        }

        return $menu;
    }

    private function getMenuItemUrl(Node $node): string
    {
        $menuItemId = $node->getId();
        if (empty($this->urls[$menuItemId])) {
            $entityType = $node->getData(EntityInterface::ENTITY_TYPE);
            $relatedEntityId = $node->getData(EntityInterface::RELATED_ENTITY_ID);
            $customLink = $node->getData(EntityInterface::CUSTOM_LINK);

            if ($entityType === 'custom' && $customLink !== null) {
                $url = $this->entityUrlBuilder->getCustomUrl($customLink);
            } elseif ($relatedEntityId !== null) {
                $url = $this->entityUrlBuilder->getEntityUrl((int)$relatedEntityId, $entityType);
            } else {
                $url = '#';
            }

            $this->urls[$menuItemId] = $url;
        }

        return $this->urls[$menuItemId];
    }

    private function getCssClass(Node $node): string
    {
        $cssClass = [];

        $level = (int)$node->getData('level');
        $isActive = $node->getIsActive();

        if ($level === Builder::LEVEL_TOP) {
            $cssClass[] = 'main-lvl';
            $cssClass[] = $node->hasChildren() ? 'has-sub-menu' : '';
        }

        if ($level === Builder::LEVEL_TAB) {
            $cssClass[] = 'tab-pane';
            $cssClass[] = 'fade';
            $cssClass[] = $node->getData('has_banner') ? 'has-banner' : '';
            $cssClass[] = $isActive ? 'show' : '';
        }

        if ($isActive) {
            $cssClass[] = 'active';
        }

        $cssClass = \array_merge($cssClass, \explode(' ', $node->getData(ItemInterface::CSS_CLASS)));
        $cssClass = \array_filter($cssClass);
        $cssClass = \array_unique($cssClass);

        return \implode(' ', $cssClass);
    }

    private function getBanner(Node $node): array
    {
        try {
            $banner = $this->bannerRepository->getByMenuItemId((int)$node->getId());
            $banner = \reset($banner);
            $data = [
                'has_banner' => true,
                'url' => $banner->getUrl(),
                'src' => $banner->getPath(),
            ];
        } catch (NoSuchEntityException $e) {
            $data = [
                'has_banner' => false,
                'url' => '',
                'src' => '',
            ];
        }

        return $data;
    }

    private function getCurrentUrl(): string
    {
        if (empty($this->currentUrl)) {
            // @see https://magento.stackexchange.com/a/118308
            $this->currentUrl = $this->getUrl('*/*/*', ['_current' => true, '_use_rewrite' => true]);
        }
        return $this->currentUrl;
    }

    private function getCurrentCategory(): \Magento\Catalog\Model\Category
    {
        return $this->layerResolver->get()->getCurrentCategory();
    }

    public function getCurrentCategoryId(): ?int
    {
        if (!isset($this->currentCategoryId)) {
            $this->currentCategoryId = (int)$this->getCurrentCategory()->getId();
        }
        return $this->currentCategoryId;
    }

    private function getCurrentPage(): Page
    {
        return $this->currentPage;
    }

    public function getCurrentPageId(): ?int
    {
        if (!isset($this->currentPageId)) {
            $this->currentPageId = (int)$this->getCurrentPage()->getId();
        }
        return $this->currentPageId;
    }

    private function getCurrentProduct(): Product
    {
        return $this->currentProduct;
    }

    public function getCurrentProductId(): ?int
    {
        if (!isset($this->currentProductId)) {
            $this->currentProductId = (int)$this->getCurrentProduct()->getId();
        }
        return $this->currentProductId;
    }
}
