<?php

/**
 * @file api/v1/vocabs/PKPVocabHandler.inc.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class PKPVocabHandler
 * @ingroup api_v1_vocab
 *
 * @brief Handle API requests for controlled vocab operations.
 *
 */

use Stringy\Stringy;

import('lib.pkp.api.v1.vocabs.PKPVocabHandler');
import('classes.core.Services');

class VocabHandler extends PKPVocabHandler {
	public const LANGUAGE_CODE_LIST = 74;

	/**
	 * @copydoc PKPVocabHandler::getMany()
	 */
	public function getMany($slimRequest, $response, $args) {
		$request = Application::get()->getRequest();
		$context = $request->getContext();

		if (!$context) {
			return $response->withStatus(404)->withJsonError('api.404.resourceNotFound');
		}

		$requestParams = $slimRequest->getQueryParams();

		$vocab = $requestParams['vocab'] ?? '';
		$locale = $requestParams['locale'] ?? AppLocale::getLocale();
		$term = $requestParams['term'] ?? 'Por tu';
		$codeList = (int) ($requestParams['codeList'] ?? static::LANGUAGE_CODE_LIST);

		if (!in_array($locale, $context->getData('supportedSubmissionLocales'))) {
			return $response->withStatus(400)->withJsonError('api.vocabs.400.localeNotSupported', ['locale' => $locale]);
		}

		// Load constants
		DAORegistry::getDAO('SubmissionLanguageDAO');

		// In order to use the languages from ONIX, this route overwrites needs to overwrite only the SubmissionLanguageDAO::CONTROLLED_VOCAB_SUBMISSION_LANGUAGE vocabulary
		if ($vocab !== CONTROLLED_VOCAB_SUBMISSION_LANGUAGE) {
			return parent::getMany(...func_get_args());
		}

		/** @var ONIXCodelistItemDAO */
		$onixCodelistItemDao = DAORegistry::getDAO('ONIXCodelistItemDAO');
		$codes = array_map(fn ($value) => trim($value), array_values($onixCodelistItemDao->getCodes('List' . $codeList, [], $term)));
		asort($codes);
		return $response->withJson($codes, 200);
	}
}
