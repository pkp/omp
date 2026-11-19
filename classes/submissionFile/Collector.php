<?php

/**
 * @file classes/submissionFile/Collector.php
 *
 * Copyright (c) 2026 Simon Fraser University
 * Copyright (c) 2026 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class Collector
 *
 * @brief A helper class to configure a Query Builder to get a collection of submission files
 */

namespace APP\submissionFile;

use Illuminate\Database\Query\Builder;
use PKP\submissionFile\Collector as BaseCollector;

class Collector extends BaseCollector
{
    /** @var null|bool get submission files with or without a direct sales price */
    protected ?bool $hasDirectSalesPrice = null;

    /**
     * Limit results to files with (true) or without (false) a direct sales price
     */
    public function filterByHasDirectSalesPrice(?bool $hasDirectSalesPrice): static
    {
        $this->hasDirectSalesPrice = $hasDirectSalesPrice;
        return $this;
    }

    /** @copydoc BaseCollector::getQueryBuilder() */
    public function getQueryBuilder(): Builder
    {
        $qb = parent::getQueryBuilder();

        if ($this->hasDirectSalesPrice !== null) {
            $this->hasDirectSalesPrice
                ? $qb->whereNotNull('sf.direct_sales_price')
                : $qb->whereNull('sf.direct_sales_price');
        }

        return $qb;
    }
}
