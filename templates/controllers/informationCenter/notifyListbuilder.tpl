{**
 * listbuilder.tpl
 *
 * Copyright (c) 2000-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Displays a listbuilder for adding users to notify in the information center.  Is stripped down to fit in the modal more easily.
 *}
{assign var="listbuilderId" value=$listbuilder->getId()}

<div id="{$listbuilderId}">
	<div class="wrapper">
		<div class="unit size2of5" id="source-{$listbuilderId}{if $itemId}-{$itemId}{/if}">
 			<ul>
		        <li>
					<span>
						<select name="selectList-{$listbuilderId}" id="selectList-{$listbuilderId}" class="field select">
							<option>{translate key='settings.setup.selectOne'}</option>
							{foreach from=$listbuilder->getPossibleItemList() item=item}{$item}{/foreach}
						</select>
					</span>
				</li>
			</ul>
		</div>
		<div class="unit size1of10 listbuilder_controls">
			<a href="#" id="add-{$listbuilderId}{if $itemId}-{$itemId}{/if}" onclick="return false;" class="add_item"></a>
			<a href="#" id="delete-{$listbuilderId}{if $itemId}-{$itemId}{/if}" onclick="return false;" class="remove_item"></a>
		</div>
		<div id="results-{$listbuilderId}{if $itemId}-{$itemId}{/if}" class="unit size1of2 lastUnit listbuilder_results">
		    <ul>
		        <li>
		            <label class="desc">
		                {$listbuilder->getListTitle()|translate}
		            </label>
					{include file="controllers/listbuilder/listbuilderGrid.tpl"}
				</li>
			</ul>
		</div>
	</div>
	<script type='text/javascript'>
	{literal}
		addItem("{/literal}{$addUrl}{literal}", "{/literal}{$listbuilderId}{if $itemId}-{$itemId}{/if}{literal}", "{/literal}{$localizedButtons}{literal}");
		deleteItems("{/literal}{$deleteUrl}{literal}", "{/literal}{$listbuilderId}{if $itemId}-{$itemId}{/if}{literal}");
		selectRow("{/literal}{$listbuilderId}{if $itemId}-{$itemId}{/if}{literal}");
	{/literal}
	</script>
</div>


