<?php

declare(strict_types=1);

namespace Scue\Menu\Ui\Component\Banner\Listing;

use Scue\Menu\Model\ResourceModel\Banner\CollectionFactory as BannerCollectionFactory;
use Magento\Framework\Api\Filter;
use Magento\Framework\Api\FilterBuilder;
use Magento\Ui\DataProvider\AbstractDataProvider;

class DataProvider extends AbstractDataProvider
{
    private FilterBuilder $filterBuilder;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        BannerCollectionFactory $collectionFactory,
        FilterBuilder $filterBuilder,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collectionFactory->create();
        $this->filterBuilder = $filterBuilder;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * @inheritdoc
     */
    public function addFilter(Filter $filter)
    {
        if ($filter->getField() === 'fulltext') {
            $titleFilter = $this->filterBuilder
                ->setField('description')
                ->setValue(sprintf('%%%s%%', $filter->getValue()))
                ->setConditionType('like')
                ->create();
            parent::addFilter($titleFilter);
        } else {
            parent::addFilter($filter);
        }
    }
}
