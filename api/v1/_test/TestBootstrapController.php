<?php

/**
 * @file api/v1/_test/TestBootstrapController.php
 *
 * Copyright (c) 2023-2026 Simon Fraser University
 * Copyright (c) 2023-2026 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class TestBootstrapController
 *
 * @ingroup api_v1__test
 *
 * @brief OMP base-seed endpoint.
 *
 * Bootstrap is a context scenario with the suite's shared roster attached, so it
 * inherits the OMP overlay (series, press settings) from PressScenarioController
 * and only changes the route and idempotency rule.
 */

namespace APP\API\v1\_test;

use Illuminate\Support\Facades\Route;

class TestBootstrapController extends PressScenarioController
{
    use \PKP\API\v1\_test\BootstrapRoutes;

    /**
     * @copydoc \PKP\core\PKPBaseController::getGroupRoutes()
     */
    public function getGroupRoutes(): void
    {
        Route::post('bootstrap', $this->bootstrap(...))->name('_test.bootstrap');
        Route::get('bootstrap', $this->status(...))->name('_test.bootstrap.status');
    }
}
