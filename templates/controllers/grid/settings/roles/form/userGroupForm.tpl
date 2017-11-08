{**
 * templates/controllers/grid/settings/roles/form/userGroupForm.tpl
 *
 * Copyright (c) 2014-2017 Simon Fraser University
 * Copyright (c) 2003-2017 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Form to edit or create a user group in OMP
 *}
{capture assign="appUserGroupOptions"}
	{fbvElement type="checkbox" label="settings.roles.isVolumeEditor" id="isVolumeEditor" checked=$isVolumeEditor}
{/capture}
{include file="core:controllers/grid/settings/roles/form/userGroupForm.tpl"}
