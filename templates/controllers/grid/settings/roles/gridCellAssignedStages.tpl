{**
 * gridRowAssignedStages.tpl
 *
 * Copyright (c) 2014 Simon Fraser University Library
 * Copyright (c) 2000-2014 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Display a table inside the grid row that shows the user group stages assignment
 *}

{foreach from=$label item=stageKey}
	<span id="{$cellId|escape}" class="pkp_controllers_grid_settings_stages">
		{if empty($stageKey)}
			-
		{else}
			{translate key=$stageKey}
		{/if}
	</span>
{/foreach}
