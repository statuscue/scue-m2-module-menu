<?php

declare(strict_types=1);

namespace Scue\Menu\Model;

use Scue\Menu\Api\Data\BannerInterface;
use Scue\Menu\Api\Data\EntityInterface;
use Scue\Menu\Model\Entity\UrlBuilder as EntityUrlBuilder;
use Magento\Framework\Model\AbstractModel;

class Banner extends AbstractModel implements BannerInterface, EntityInterface
{
    protected $_eventPrefix = 'scue_menu_banner';
    protected $_idFieldName = BannerInterface::ID;

    private EntityUrlBuilder $entityUrlBuilder;

    /**
     * @noinspection MagicMethodsValidityInspection
     */
    protected function _construct()
    {
        $this->_init(\Scue\Menu\Model\ResourceModel\Banner::class);
    }

    public function __construct(
        EntityUrlBuilder $entityUrlBuilder,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->entityUrlBuilder = $entityUrlBuilder;
    }

    /**
     * @inheritDoc
     */
    public function getPath(): string
    {
        return $this->getData(BannerInterface::PATH);
    }

    /**
     * @inheritDoc
     */
    public function setPath(string $path): BannerInterface
    {
        return $this->setData(BannerInterface::PATH, $path);
    }

    /**
     * @inheritDoc
     */
    public function getDescription(): string
    {
        return $this->getData(BannerInterface::DESCRIPTION);
    }

    /**
     * @inheritDoc
     */
    public function setDescription(?string $description): BannerInterface
    {
        return $this->setData(BannerInterface::DESCRIPTION, $description);
    }

    /**
     * @inheritDoc
     */
    public function isActive(): bool
    {
        return (int)$this->getData(BannerInterface::IS_ACTIVE) === 1;
    }

    /**
     * @inheritDoc
     */
    public function setActiveStatus(int $status): BannerInterface
    {
        return $this->setData(BannerInterface::IS_ACTIVE, $status);
    }

    /**
     * @inheritDoc
     */
    public function enable(): BannerInterface
    {
        return $this->setActiveStatus(1);
    }

    /**
     * @inheritDoc
     */
    public function disable(): BannerInterface
    {
        return $this->setActiveStatus(0);
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
    public function getUrl(): string
    {
        if (!$this->hasData('_url')) {
            if ($this->getEntityType() === 'custom' && $this->getCustomLink() !== null) {
                $this->setData('_url', $this->entityUrlBuilder->getCustomUrl($this->getCustomLink()));
            } elseif ($this->getRelatedEntityId() !== null) {
                $this->setData('_url', $this->entityUrlBuilder->getEntityUrl(
                    (int)$this->getRelatedEntityId(),
                    $this->getEntityType()
                ));
            } else {
                $this->setData('_url', '#');
            }
        }
        return $this->getData('_url');
    }
}
