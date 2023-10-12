<?php
/**
 * @defgroup api_v1_temporaryFiles Temporary file upload API requests
 */
/**
 * @file api/v1/temporaryFiles/index.php
 *
 * Copyright (c) 2023 Simon Fraser University
 * Copyright (c) 2023 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @ingroup api_v1_temporaryFiles
 *
 * @brief Handle API requests for temporary file uploading.
 */

return new \PKP\handler\APIHandler(new \PKP\API\v1\temporaryFiles\PKPTemporaryFilesController());
