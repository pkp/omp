<?php

/**
 * @file api/v1/vocab/VocabHandler.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class VocabHandler
 *
 * @ingroup api_v1_vocab
 *
 * @brief Handle API requests for vocab operations.
 *
 */

namespace APP\API\v1\vocabs;

use APP\codelist\ONIXCodelistItemDAO;
use APP\core\Application;
use PKP\core\APIResponse;
use PKP\db\DAORegistry;
use PKP\facades\Locale;
use PKP\submission\SubmissionLanguageDAO;
use Slim\Http\Request;

class VocabHandler extends \PKP\API\v1\vocabs\PKPVocabHandler
{
    public const LANGUAGE_CODE_LIST = 74;

    /**
     * @copydoc PKPVocabHandler::getMany()
     */
    public function getMany(Request $slimRequest, APIResponse $response, array $args): APIResponse
    {
        $request = Application::get()->getRequest();
        $context = $request->getContext();

        if (!$context) {
            return $response->withStatus(404)->withJsonError('api.404.resourceNotFound');
        }

        $requestParams = $slimRequest->getQueryParams();

        $vocab = $requestParams['vocab'] ?? '';
        $locale = $requestParams['locale'] ?? Locale::getLocale();
        $term = $requestParams['term'] ?? null;
        $codeList = (int) ($requestParams['codeList'] ?? static::LANGUAGE_CODE_LIST);

        if (!in_array($locale, $context->getData('supportedSubmissionLocales'))) {
            return $response->withStatus(400)->withJsonError('api.vocabs.400.localeNotSupported', ['locale' => $locale]);
        }

        // In order to use the languages from ONIX, this route overwrites needs to overwrite only the SubmissionLanguageDAO::CONTROLLED_VOCAB_SUBMISSION_LANGUAGE vocabulary
        if ($vocab !== SubmissionLanguageDAO::CONTROLLED_VOCAB_SUBMISSION_LANGUAGE) {
            return parent::getMany(...func_get_args());
        }

        /** @var ONIXCodelistItemDAO */
        $onixCodelistItemDao = DAORegistry::getDAO('ONIXCodelistItemDAO');
        $codes = array_map(fn ($value) => trim($value), array_values($onixCodelistItemDao->getCodes('List' . $codeList, [], $term)));
        asort($codes);
        return $response->withJson($codes, 200);
    }
}
