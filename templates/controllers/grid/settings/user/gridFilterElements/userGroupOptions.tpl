{**
 * controllers/grid/settings/user/gridFilterElements/userGroupOptions.tpl
 *
 * Copyright (c) 2000-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Grid filter user group options.
 *}
{fbvFormSection}
	{fbvElement type="select" name="userGroup" id="userGroup" from=$filterData.userGroupOptions selected=$filterSelectionData.userGroup translate=false}
{/fbvFormSection}