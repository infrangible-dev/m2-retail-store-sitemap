<?php

declare(strict_types=1);

namespace Infrangible\RetailStoreSitemap\Model;

use Infrangible\Core\Helper\Stores;
use Infrangible\RetailStore\Model\ResourceModel\Store\CollectionFactory;
use Infrangible\RetailStore\Model\Store;
use Infrangible\Sitemap\Model\ISitemapUrl;
use Infrangible\Sitemap\Model\SitemapUrlFactory;
use Infrangible\Sitemap\Model\Source\ISource;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2025 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Source implements ISource
{
    /** @var Stores */
    protected $storeHelper;

    /** @var CollectionFactory */
    protected $retailStoreCollectionFactory;

    /** @var SitemapUrlFactory */
    protected $sitemapUrlFactory;

    public function __construct(
        CollectionFactory $retailStoreCollectionFactory,
        SitemapUrlFactory $sitemapUrlFactory,
        Stores $storeHelper
    ) {
        $this->retailStoreCollectionFactory = $retailStoreCollectionFactory;
        $this->sitemapUrlFactory = $sitemapUrlFactory;
        $this->storeHelper = $storeHelper;
    }

    public function getStoreData(int $storeId): array
    {
        $export = $this->storeHelper->getStoreConfigFlag(
            'infrangible_sitemap/retail_store/export',
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

        $items = [];

        foreach ($collection->getItems() as $page) {
            if ($page->getData('exclude_from_sitemap')) {
                continue;
            }

            $items[] = $page;
        }

        return $items;
    }

    /**
     * @param Store[] $storeData
     *
     * @return ISitemapUrl[]
     */
    public function transformStoreData(int $storeId, array $storeData): array
    {
        $changeFrequency = $this->storeHelper->getStoreConfig(
            'infrangible_sitemap/retail_store/changeFrequency',
            'yearly',
            false,
            $storeId
        );
        $priority = $this->storeHelper->getStoreConfig(
            'infrangible_sitemap/retail_store/priority',
            '0.5',
            false,
            $storeId
        );

        $urls = [];

        foreach ($storeData as $retailStore) {
            $sitemapUrl = $this->sitemapUrlFactory->create();

            $sitemapUrl->setUrl($retailStore->getUrl());
            $sitemapUrl->setLastModified(
                date(
                    'Y-m-d',
                    strtotime($retailStore->getUpdatedAt())
                )
            );
            $sitemapUrl->setChangeFrequency($changeFrequency);
            $sitemapUrl->setPriority($priority);

            $urls[] = $sitemapUrl;
        }

        return $urls;
    }
}
