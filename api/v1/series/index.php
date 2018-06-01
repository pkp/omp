<?php

/**
 * @defgroup api_v1_series Series API requests
 */

/**
 * @file api/v1/series/index.php
 *
 * Copyright (c) 2014-2018 Simon Fraser University
 * Copyright (c) 2003-2018 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @ingroup api_v1_series
 * @brief Handle requests for series API functions.
 *
 */

import('api.v1.series.SeriesHandler');
return new SeriesHandler();
