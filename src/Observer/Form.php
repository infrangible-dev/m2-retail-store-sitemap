<?php

declare(strict_types=1);

namespace Infrangible\RetailStoreSitemap\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2025 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Form implements ObserverInterface
{
    public function execute(Observer $observer)
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $observer->getData('form');

        $fieldSet = $form->addFieldset(
            'sitemap',
            ['legend' => __('Sitemap')]
        );

        /** @var \Infrangible\BackendWidget\Block\Form $block */
        $block = $observer->getEvent()->getBlock();

        $block->addYesNoDefaultField(
            $fieldSet,
            'exclude_from_sitemap',
            __('Exclude from Sitemap')->render(),
            0
        );
    }
}
