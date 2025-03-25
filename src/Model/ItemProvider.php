<?php

declare(strict_types=1);

namespace Infrangible\RetailStoreSitemap\Model;

use Infrangible\Core\Helper\Stores;
use Infrangible\RetailStore\Model\ResourceModel\Store\CollectionFactory;
use Infrangible\RetailStore\Model\Store;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sitemap\Model\ItemProvider\ItemProviderInterface;
use Magento\Sitemap\Model\SitemapItemInterfaceFactory;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2025 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class ItemProvider implements ItemProviderInterface
{
    /** @var Stores */
    protected $storeHelper;

    /** @var CollectionFactory */
    protected $retailStoreCollectionFactory;

    /** @var SitemapItemInterfaceFactory */
    protected $itemFactory;

    public function __construct(
        Stores $storeHelper,
        CollectionFactory $retailStoreCollectionFactory,
        SitemapItemInterfaceFactory $itemFactory
    ) {
        $this->storeHelper = $storeHelper;
        $this->retailStoreCollectionFactory = $retailStoreCollectionFactory;
        $this->itemFactory = $itemFactory;
    }

    /**
     * @throws LocalizedException
     */
    public function getItems($storeId): array
    {
        $storeId = (int)$storeId;

        $export = $this->storeHelper->getStoreConfigFlag(
            'sitemap/retail_store/export',
            true,
            $storeId
        );

        if (! $export) {
            return [];
        }

        $collection = $this->retailStoreCollectionFactory->create();

        $collection->addActiveFilter();
        $collection->addAttributeToSelect('url_key');
        $collection->addAttributeToSelect('exclude_from_sitemap');

        $changeFrequency = $this->storeHelper->getStoreConfig(
            'sitemap/retail_store/changeFrequency',
            'yearly',
            false,
            $storeId
        );
        $priority = $this->storeHelper->getStoreConfig(
            'sitemap/retail_store/priority',
            '0.5',
            false,
            $storeId
        );

        $items = [];

        /** @var Store $retailStore */
        foreach ($collection as $retailStore) {
            if ($retailStore->getData('exclude_from_sitemap')) {
                continue;
            }

            $items[] = $this->itemFactory->create(
                [
                    'url'             => $retailStore->getUrlPath(),
                    'updatedAt'       => $retailStore->getUpdatedAt(),
                    'changeFrequency' => $changeFrequency,
                    'priority'        => $priority
                ]
            );
        }

        return $items;
    }
}
