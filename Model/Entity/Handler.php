<?php

declare(strict_types=1);

namespace Scue\Menu\Model\Entity;

/**
 * Handle the "urlInput" component data.
 *
 * This component is used in both menu item and banner form.
 */
class Handler
{
    public const DEFAULT_ENTITY_TYPE = 'default';

    private array $entity;

    public function __construct(array $entity = [])
    {
        $this->entity = $entity;
    }

    public function getEntity(): array
    {
        return $this->entity;
    }

    public function setEntity(array $entity): void
    {
        $this->entity = $entity;
    }

    public function getEntityType(): string
    {
        return !empty($this->entity) && \array_key_exists('type', $this->entity) && !empty($this->entity['type'])
            ? $this->entity['type']
            : self::DEFAULT_ENTITY_TYPE;
    }

    public function getCustomLink(): ?string
    {
        $customLink = null;
        if (!empty($this->entity)
            // do not return custom link if type is different
            && (\array_key_exists('type', $this->entity) && $this->entity['type'] === self::DEFAULT_ENTITY_TYPE)
            && \array_key_exists('default', $this->entity)
            && !empty($this->entity['default'])
        ) {
            $customLink = $this->entity['default'];
        }
        return $customLink;
    }

    public function getRelatedEntityIdByType(string $entityType): ?int
    {
        $relatedEntityId = null;
        if (!empty($this->entity)
            // return entity id for any type, except for "custom"
            && (\array_key_exists('type', $this->entity) && $this->entity['type'] !== self::DEFAULT_ENTITY_TYPE)
            && \array_key_exists($entityType, $this->entity)
            && !empty($this->entity[$entityType])
        ) {
            $relatedEntityId = (int)$this->entity[$entityType];
        }
        return $relatedEntityId;
    }
}
