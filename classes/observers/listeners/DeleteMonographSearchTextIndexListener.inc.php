<?php

declare(strict_types=1);

/**
 * @file classes/observers/listeners/DeleteMonographSearchTextIndexListener.inc.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class DeleteMonographSearchTextIndexListener
 * @ingroup core
 *
 * @brief Listener fired when monograph was deleted
 */

namespace APP\observers\listeners;

use APP\jobs\monograph\DeleteMonographSearchTextIndexJob;
use APP\observers\events\DeleteMonographSearchTextIndex;
use Illuminate\Events\Dispatcher;

class DeleteMonographSearchTextIndexListener
{
    /**
     * Maps methods with correspondent events to listen
     */
    public function subscribe(Dispatcher $events): void
    {
        $events->listen(
            DeleteMonographSearchTextIndex::class,
            self::class . '@handle'
        );
    }

    /**
     * Handle the listener call
     */
    public function handle(DeleteMonographSearchTextIndex $event)
    {
        dispatch(new DeleteMonographSearchTextIndexJob($event->monographId));
    }
}
