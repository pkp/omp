<?php

declare(strict_types=1);

/**
 * @file classes/observers/events/DeleteMonographSearchTextIndex.inc.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class DeleteMonographSearchTextIndex
 * @ingroup core
 *
 * @brief Event for monograph search text index deleting
 */

namespace APP\observers\events;

use Illuminate\Foundation\Events\Dispatchable;

class DeleteMonographSearchTextIndex
{
    use Dispatchable;

    /** @var int $monographId Monograph's Id */
    public $monographId;

    /**
     * Class construct
     *
     * @param int $monographId Monograph's Id
     */
    public function __construct(
        int $monographId
    ) {
        $this->monographId = $monographId;
    }
}
