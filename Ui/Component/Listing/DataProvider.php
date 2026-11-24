<?php

declare(strict_types=1);

namespace Scue\Menu\Ui\Component\Listing;

use Scue\Menu\Model\ResourceModel\Item\CollectionFactory;
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
        CollectionFactory $collectionFactory,
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
                ->setField('title')
                ->setValue(sprintf('%%%s%%', $filter->getValue()))
                ->setConditionType('like')
                ->create();
            parent::addFilter($titleFilter);
        } else {
            parent::addFilter($filter);
        }
    }
}
