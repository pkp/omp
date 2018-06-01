<?php

/**
 * @defgroup api_v1_categories Category API requests
 */

/**
 * @file api/v1/categories/index.php
 *
 * Copyright (c) 2014-2018 Simon Fraser University
 * Copyright (c) 2003-2018 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @ingroup api_v1_categories
 * @brief Handle requests for category API functions.
 *
 */

import('api.v1.categories.CategoryHandler');
return new CategoryHandler();
