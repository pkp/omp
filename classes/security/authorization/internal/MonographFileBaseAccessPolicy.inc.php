<?php
/**
 * @file classes/security/authorization/internal/MonographFileBaseAccessPolicy.inc.php
 *
 * Copyright (c) 2000-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MonographFileBaseAccessPolicy
 * @ingroup security_authorization_internal
 *
 * @brief Abstract class for monograph file access policies.
 *
 * NB: This policy expects a previously authorized monograph in the
 * authorization context.
 */

import('lib.pkp.classes.security.authorization.AuthorizationPolicy');

class MonographFileBaseAccessPolicy extends AuthorizationPolicy {
	/** @var PKPRequest */
	var $_request;

	/**
	 * Constructor
	 * @param $request PKPRequest
	 */
	function MonographFileBaseAccessPolicy(&$request) {
		parent::AuthorizationPolicy('user.authorization.monographFile');
		$this->_request =& $request;
	}


	//
	// Private methods
	//
	/**
	 * Get a cache of monograph files. Used because many policy subclasses
	 * may be combined to fetch a single monograph file.
	 * @return array
	 */
	function &_getCache() {
		static $cache;
		if (!isset($cache)) $cache = array();
		return $cache;
	}


	//
	// Protected methods
	//
	/**
	 * Get the requested monograph file.
	 * @param $request PKPRequest
	 * @return MonographFile
	 */
	function &getMonographFile(&$request) {
		// Get the identifying info from the request
		$fileId = (int) $request->getUserVar('fileId');
		$revision = (int) $request->getUserVar('revision');
		assert($fileId);
		$cacheId = "$fileId-$revision"; // -0 for most recent revision

		// Fetch the object, caching if possible
		$cache =& $this->_getCache();
		if (!isset($cache[$cacheId])) {
			// Cache miss
			$submissionFileDao =& DAORegistry::getDAO('SubmissionFileDAO');
			if ($revision) {
				$cache[$cacheId] =& $submissionFileDao->getRevision($fileId, $revision);
			} else {
				$cache[$cacheId] =& $submissionFileDao->getLatestRevision($fileId);
			}
		}

		return $cache[$cacheId];
	}

	/**
	 * Get the current request object.
	 * @return PKPRequest
	 */
	function &getRequest() {
		return $this->_request;
	}
}

?>
