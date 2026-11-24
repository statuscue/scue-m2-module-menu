<?php

namespace Scue\Menu\Api\Data;

/**
 * General menu item interface.
 */
interface ItemInterface
{
    /**#@+
     * Field names of `scue_menu_items` table in database.
     * @var string
     */
    public const ID = 'menu_item_id';
    public const TITLE = 'title';
    public const PARENT_ID = 'parent_id';
    public const CSS_CLASS = 'css_class';
    public const SORT_ORDER = 'sort_order';
    public const BG_COLOR = 'bg_color';
    public const BG_IMAGE = 'bg_image';
    /**#@-*/

    /**
     * The value of `parent_id` for root (first level) menu items.
     * @var int
     */
    public const NO_PARENT_ID = 0;

    /**
     * Get entry Id.
     * @return int|null
     */
    public function getId();

    /**
     * Set entry Id.
     * @param int|null $id
     * @return ItemInterface
     */
    public function setId(?int $id);

    /**
     * Get title.
     * @return string
     */
    public function getTitle(): string;

    /**
     * Set title.
     * @param string|null $title
     * @return ItemInterface
     */
    public function setTitle(?string $title): ItemInterface;

    /**
     * Get parent menu item Id.
     * @return int
     */
    public function getParentId(): int;

    /**
     * Set parent menu item Id.
     * @param int $parentId
     * @return ItemInterface
     */
    public function setParentId(int $parentId = 0): ItemInterface;

    /**
     * Get CSS class.
     * @return string
     */
    public function getCssClass(): string;

    /**
     * Set CSS class.
     * @param string|null $cssClass
     * @return ItemInterface
     */
    public function setCssClass(?string $cssClass): ItemInterface;

    /**
     * Get sort order.
     * @return int
     */
    public function getSortOrder(): int;

    /**
     * Set sort order.
     * @param int $order
     * @return ItemInterface
     */
    public function setSortOrder(int $order = 10): ItemInterface;

    /**
     * Get background color (hex).
     * @return string|null
     */
    public function getBgColor(): ?string;

    /**
     * Set background color (hex).
     * @param string|null $color
     * @return ItemInterface
     */
    public function setBgColor(?string $color): ItemInterface;

    /**
     * Get relative path to background image.
     * @return string|null
     */
    public function getBgImage(): ?string;

    /**
     * Set relative path to background image.
     * @param string|null $bgImage
     * @return ItemInterface
     */
    public function setBgImage(?string $bgImage): ItemInterface;
}
