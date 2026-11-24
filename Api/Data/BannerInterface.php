<?php

namespace Scue\Menu\Api\Data;

interface BannerInterface
{
    /**#@+
     * Field names of `scue_menu_banners` table in database.
     * @var string
     */
    public const ID = 'banner_id';
    public const MENU_ITEM_ID = 'menu_item_id';
    public const PATH = 'path';
    public const DESCRIPTION = 'description';
    public const IS_ACTIVE = 'is_active';
    /**#@-*/

    /**
     * Get entry Id.
     * @return int|null
     */
    public function getId();

    /**
     * Set entry Id.
     * @param int|null $id
     * @return BannerInterface
     */
    public function setId($id);

    /**
     * Get path to banner.
     * @return string
     */
    public function getPath(): string;

    /**
     * Set path to banner.
     * @param string $path
     * @return BannerInterface
     */
    public function setPath(string $path): BannerInterface;

    /**
     * Get description.
     * @return string
     */
    public function getDescription(): string;

    /**
     * Set description.
     * @param string|null $description
     * @return BannerInterface
     */
    public function setDescription(?string $description): BannerInterface;

    /**
     * Check active status.
     * @return bool
     */
    public function isActive(): bool;

    /**
     * Set active status.
     * @param int $status 0 or 1.
     * @return BannerInterface
     */
    public function setActiveStatus(int $status): BannerInterface;

    /**
     * Enable banner.
     * @return BannerInterface
     */
    public function enable(): BannerInterface;

    /**
     * Disable banner.
     * @return BannerInterface
     */
    public function disable(): BannerInterface;

    /**
     * Get banner URL, based on entity type.
     *
     * @return string
     */
    public function getUrl(): string;
}
