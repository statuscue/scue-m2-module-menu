<?php

declare(strict_types=1);

namespace Scue\Menu\Model;

use Scue\Menu\Api\Data\EntityInterface;
use Scue\Menu\Api\Data\ItemInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * Menu item model.
 */
class Item extends AbstractModel implements ItemInterface, EntityInterface
{
    protected $_eventPrefix = 'scue_menu_item';
    protected $_idFieldName = ItemInterface::ID;

    /**
     * @noinspection MagicMethodsValidityInspection
     */
    protected function _construct()
    {
        $this->_init(\Scue\Menu\Model\ResourceModel\Item::class);
    }

    /**
     * @inheritDoc
     */
    public function getTitle(): string
    {
        return $this->getData(ItemInterface::TITLE);
    }

    /**
     * @inheritDoc
     */
    public function setTitle(?string $title): ItemInterface
    {
        return $this->setData(ItemInterface::TITLE, $title);
    }

    /**
     * @inheritDoc
     */
    public function getParentId(): int
    {
        return (int)$this->getData(ItemInterface::PARENT_ID);
    }

    /**
     * @inheritDoc
     */
    public function setParentId(int $parentId = 0): ItemInterface
    {
        return $this->setData(ItemInterface::PARENT_ID, $parentId);
    }

    /**
     * @inheritDoc
     */
    public function getCssClass(): string
    {
        return $this->getData(ItemInterface::CSS_CLASS);
    }

    /**
     * @inheritDoc
     */
    public function setCssClass(?string $cssClass): ItemInterface
    {
        return $this->setData(ItemInterface::CSS_CLASS, $cssClass);
    }

    /**
     * @inheritDoc
     */
    public function getSortOrder(): int
    {
        return (int)$this->getData(ItemInterface::SORT_ORDER);
    }

    /**
     * @inheritDoc
     */
    public function setSortOrder(int $order = 10): ItemInterface
    {
        return $this->setData(ItemInterface::SORT_ORDER, $order);
    }

    /**
     * @inheritDoc
     */
    public function getEntityType(): string
    {
        return $this->getData(EntityInterface::ENTITY_TYPE);
    }

    /**
     * @inheritDoc
     */
    public function setEntityType(string $type): EntityInterface
    {
        return $this->setData(EntityInterface::ENTITY_TYPE, $type);
    }

    /**
     * @inheritDoc
     */
    public function getCustomLink(): ?string
    {
        return $this->getData(EntityInterface::CUSTOM_LINK);
    }

    /**
     * @inheritDoc
     */
    public function setCustomLink(?string $link): EntityInterface
    {
        return $this->setData(EntityInterface::CUSTOM_LINK, $link);
    }

    /**
     * @inheritDoc
     */
    public function getRelatedEntityId(): ?int
    {
        $relatedEntityId = $this->getData(EntityInterface::RELATED_ENTITY_ID);
        return $relatedEntityId !== null ? (int)$relatedEntityId : null;
    }

    /**
     * @inheritDoc
     */
    public function setRelatedEntityId(?int $relatedEntityId): EntityInterface
    {
        return $this->setData(EntityInterface::RELATED_ENTITY_ID, $relatedEntityId);
    }

    /**
     * @inheritDoc
     */
    public function getBgColor(): ?string
    {
        return $this->getData(ItemInterface::BG_COLOR);
    }

    /**
     * @inheritDoc
     */
    public function setBgColor(?string $color): ItemInterface
    {
        return $this->setData(ItemInterface::BG_COLOR, $color);
    }

    /**
     * @inheritDoc
     */
    public function getBgImage(): ?string
    {
        return $this->getData(ItemInterface::BG_IMAGE);
    }

    /**
     * @inheritDoc
     */
    public function setBgImage(?string $bgImage): ItemInterface
    {
        return $this->setData(ItemInterface::BG_IMAGE, $bgImage);
    }
}
