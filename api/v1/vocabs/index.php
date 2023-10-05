<?php
/**
 * @defgroup api_v1_vocabs Controlled vocabulary API requests
 */

/**
 * @file api/v1/vocabs/index.php
 *
 * Copyright (c) 2023 Simon Fraser University
 * Copyright (c) 2023 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @ingroup api_v1_vocabs
 *
 * @brief Handle API requests for vocabs.
 */

<<<<<<< HEAD
return new \PKP\handler\APIHandler(new \APP\API\v1\vocabs\VocabController());
=======
return new \PKP\API\v1\vocabs\PKPVocabHandler();
>>>>>>> deb8671e8 (pkp/pkp-lib#5000  Remove separate Dublin Core Language metadata field and only use Submission Locale to define the language)
