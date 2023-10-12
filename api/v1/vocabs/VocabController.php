<?php

/**
 * @file api/v1/vocab/VocabController.php
 *
 * Copyright (c) 2023 Simon Fraser University
 * Copyright (c) 2023 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class VocabController
 *
 * @ingroup api_v1_vocab
 *
 * @brief Handle API requests for vocab operations.
 *
 */

namespace APP\API\v1\vocabs;

use APP\codelist\ONIXCodelistItemDAO;
use APP\core\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use PKP\db\DAORegistry;
use PKP\facades\Locale;
use PKP\submission\SubmissionLanguageDAO;

class VocabController extends \PKP\API\v1\vocabs\PKPVocabController
{
    public const LANGUAGE_CODE_LIST = 74;

    /**
     * @copydoc \PKP\API\v1\vocabs\PKPVocabController::getMany()
     */
    public function getMany(Request $illuminateRequest): JsonResponse
    {
        $request = Application::get()->getRequest();
        $context = $request->getContext();

        if (!$context) {
            return response()->json([
                'error' => __('api.404.resourceNotFound'),
            ], Response::HTTP_NOT_FOUND);
        }

        $requestParams = $illuminateRequest->query();

        $vocab = $requestParams['vocab'] ?? '';
        $locale = $requestParams['locale'] ?? Locale::getLocale();
        $term = $requestParams['term'] ?? null;
        $codeList = (int) ($requestParams['codeList'] ?? static::LANGUAGE_CODE_LIST);

        if (!in_array($locale, $context->getData('supportedSubmissionLocales'))) {
            return response()->json([
                'error' => __('api.vocabs.400.localeNotSupported', ['locale' => $locale]),
            ], Response::HTTP_BAD_REQUEST);
        }

        // In order to use the languages from ONIX, this route overwrites needs to overwrite only the SubmissionLanguageDAO::CONTROLLED_VOCAB_SUBMISSION_LANGUAGE vocabulary
        if ($vocab !== SubmissionLanguageDAO::CONTROLLED_VOCAB_SUBMISSION_LANGUAGE) {
            return parent::getMany(...func_get_args());
        }

        /** @var ONIXCodelistItemDAO */
        $onixCodelistItemDao = DAORegistry::getDAO('ONIXCodelistItemDAO');
        $codes = array_map(fn ($value) => trim($value), array_values($onixCodelistItemDao->getCodes('List' . $codeList, [], $term)));
        asort($codes);

        return response()->json($codes, Response::HTTP_OK);
    }
}
