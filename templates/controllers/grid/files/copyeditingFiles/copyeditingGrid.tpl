<table id="component-copyeditingFiles-table">
	<colgroup>
		{"<col />"|str_repeat:$numColumns}
	</colgroup>
	<thead>
		{** build the column headers **}
		<tr>
			{foreach name=columns from=$columns item=column}
				<th scope="col">
					{$column->getLocalizedTitle()}
					{if $smarty.foreach.columns.last && $grid->getActions($smarty.const.GRID_ACTION_POSITION_LASTCOL)}
						<span class="options">
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