<?php

/**
 * @file classes/press/SocialMedia.inc.php
 *
 * Copyright (c) 2014-2016 Simon Fraser University Library
 * Copyright (c) 2003-2016 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SocialMedia
 * @ingroup context
 * @see SocialMediaDAO
 *
 * @brief Describes basic SocialMedia properties.
 */

import('lib.pkp.classes.context.PKPSocialMedia');

class SocialMedia extends PKPSocialMedia {
	/**
	 * Constructor.
	 */
	function SocialMedia() {
		parent::PKPSocialMedia();
	}

	/**
	 * Replace various variables in the code template with data
	 * relevant to the assigned monograph.
	 * @param PublishedMonograph $publishedMonograph
	 */
	function replaceCodeVars($publishedMonograph = null) {

		$application = Application::getApplication();
		$request = $application->getRequest();
		$router = $request->getRouter();
		$context = $request->getContext();

		$code = $this->getCode();

		$codeVariables = array(
			'contextUrl' => $router->url($request, null, 'index'),
			'pressName' => $context->getLocalizedName(),
		);

		if (isset($publishedMonograph)) {
			$codeVariables = array_merge($codeVariables, array(
				'bookCatalogUrl' => $router->url($request, null, 'catalog', 'book', $publishedMonograph->getId()),
				'bookTitle' => $publishedMonograph->getLocalizedTitle(),
			));
		}

		// Replace variables in message with values
		foreach ($codeVariables as $key => $value) {
			if (!is_object($value)) {
				$code = str_replace('{$' . $key . '}', $value, $code);
			}
		}

		$this->setCode($code);
	}
}

?>
