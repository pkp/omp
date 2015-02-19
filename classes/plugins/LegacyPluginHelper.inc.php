<?php

/**
 * @file classes/plugins/LegacyPluginHelper.inc.php
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class LegacyPluginHelper
 * @ingroup plugins
 *
 * @brief Helper class for plugins that don't have their UI adapted to OMP.
 *
 * FIXME: After modernizing the UI of the plugins, remove this class.
 */

class LegacyPluginHelper {


	//
	// Static public methods.
	//
	/**
	 * Returns a JSON message with an event to refresh the
	 * modal where the plugin content is.
	 * This will simulate the Request::redirect behaviour,
	 * using the existing modal to load the new content.
	 *
	 * @param $url The url that will be used to fetch new content.
	 * @return string
	 */
	function redirect($url) {
		$json = new JSONMessage(true);
		$json->setEvent('refreshLegacyPluginModal', $url);

		return $json->getString();
	}

}

?>
