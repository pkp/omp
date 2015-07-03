<?php
/**
 * @file classes/security/authorization/OmpPluginAccessPolicy.inc.php
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2000-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class OmpPluginAccessPolicy
 * @ingroup security_authorization
 *
 * @brief Class to control access to OMP's plugins.
 */

import('lib.pkp.classes.security.authorization.PolicySet');
import('classes.security.authorization.internal.PluginLevelRequiredPolicy');
import('lib.pkp.classes.security.authorization.internal.PluginRequiredPolicy');
import('lib.pkp.classes.security.authorization.RoleBasedHandlerOperationPolicy');

define('ACCESS_MODE_MANAGE', 0x01);
define('ACCESS_MODE_ADMIN', 0x02);

class OmpPluginAccessPolicy extends PolicySet {
	/**
	 * Constructor
	 * @param $request PKPRequest
	 * @param $args array request arguments
	 * @param $roleAssignments array
	 * @param $accessMode int
	 */
	function OmpPluginAccessPolicy($request, &$args, $roleAssignments, $accessMode = ACCESS_MODE_ADMIN) {
		parent::PolicySet();

		// A valid plugin is required.
		$this->addPolicy(new PluginRequiredPolicy($request));

		// Press managers and site admin have
		// access to plugins. We'll have to define
		// differentiated policies for those roles in a policy set.
		$pluginAccessPolicy = new PolicySet(COMBINING_PERMIT_OVERRIDES);
		$pluginAccessPolicy->setEffectIfNoPolicyApplies(AUTHORIZATION_DENY);

		//
		// Managerial role
		//
		if (isset($roleAssignments[ROLE_ID_MANAGER])) {
			if ($accessMode & ACCESS_MODE_MANAGE) {
				// Press managers have edit settings access mode...
				$pressManagerPluginAccessPolicy = new PolicySet(COMBINING_DENY_OVERRIDES);
				$pressManagerPluginAccessPolicy->addPolicy(new RoleBasedHandlerOperationPolicy($request, ROLE_ID_MANAGER, $roleAssignments[ROLE_ID_MANAGER]));

				// ...only to press level plugins.
				$pressManagerPluginAccessPolicy->addPolicy(new PluginLevelRequiredPolicy($request, CONTEXT_PRESS));

				$pluginAccessPolicy->addPolicy($pressManagerPluginAccessPolicy);
			}
		}

		//
		// Site administrator role
		//
		if (isset($roleAssignments[ROLE_ID_SITE_ADMIN])) {
			// Site admin have access to all plugins...
			$siteAdminPluginAccessPolicy = new PolicySet(COMBINING_DENY_OVERRIDES);
			$siteAdminPluginAccessPolicy->addPolicy(new RoleBasedHandlerOperationPolicy($request, ROLE_ID_SITE_ADMIN, $roleAssignments[ROLE_ID_SITE_ADMIN]));

			if ($accessMode & ACCESS_MODE_MANAGE) {
				// ...of site level only.
				$siteAdminPluginAccessPolicy->addPolicy(new PluginLevelRequiredPolicy($request, CONTEXT_SITE));
			}

			$pluginAccessPolicy->addPolicy($siteAdminPluginAccessPolicy);
		}

		$this->addPolicy($pluginAccessPolicy);
	}
}

?>
