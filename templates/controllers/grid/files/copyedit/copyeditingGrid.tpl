{**
 * templates/controllers/grid/files/copyedit.tpl
 *
 * Copyright (c) 2000-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Copyediting grid
 *}
<table id="component-copyeditingFiles-table">
	<colgroup>
		{foreach from=$columns item=column}<col />{/foreach}
	</colgroup>
	<thead>
		{** build the column headers **}
		<tr>
			{foreach name=columns from=$columns item=column}
				<th scope="col">
					{$column->getLocalizedTitle()}
					{if $smarty.foreach.columns.last && $grid->getActions($smarty.const.GRID_ACTION_POSITION_LASTCOL)}
						<span class="options pkp_linkActions">
							{foreach from=$grid->getActions($smarty.const.GRID_ACTION_POSITION_LASTCOL) item=action}
								{if $action->getMode() eq $smarty.const.LINK_ACTION_MODE_AJAX}
									{assign var=actionActOnId value=$action->getActOn()}
								{else}
									{assign var=actionActOnId value=$gridActOnId}
								{/if}
								{include file="linkAction/legacyLinkAction.tpl" action=$action id=$gridId actOnId=$actionActOnId hoverTitle=true}
							{/foreach}
						</span>
					{/if}
				</th>
			{/foreach}
		</tr>
	</thead>
	{$renderedGridRows}
</table>
