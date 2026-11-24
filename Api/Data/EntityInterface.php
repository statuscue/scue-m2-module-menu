<?php

namespace Scue\Menu\Api\Data;

interface EntityInterface
{
    /**#@+
     * Constants
     *
     * @var string
     */
    public const ENTITY_TYPE = 'entity_type';
    public const RELATED_ENTITY_ID = 'related_entity_id';
    public const CUSTOM_LINK = 'custom_link';
    /**#@-*/

    /**
     * Get entity type
     *
     * @return string
     */
    public function getEntityType(): string;

    /**
     * Set entity type
     *
     * @param string $type
     * @return EntityInterface
     */
    public function setEntityType(string $type): EntityInterface;

    /**
     * Get a custom link
     *
     * @return string|null
     */
    public function getCustomLink(): ?string;

    /**
     * Set a custom link
     *
     * @param string|null $link
     * @return EntityInterface
     */
    public function setCustomLink(?string $link): EntityInterface;

    /**
     * Get related entity id
     *
     * @return int|null
     */
    public function getRelatedEntityId(): ?int;

    /**
     * Set related entity id
     *
     * @param int|null $relatedEntityId
     * @return EntityInterface
     */
    public function setRelatedEntityId(?int $relatedEntityId): EntityInterface;
}
