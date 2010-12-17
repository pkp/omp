{**
 * listbuilder.tpl
 *
 * Copyright (c) 2000-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Displays a listbuilder for adding users to notify in the information center.  Is stripped down to fit in the modal more easily.
 *}
{assign var="listbuilderId" value=$listbuilder->getId()}

<div id="{$listbuilderId|escape}">
	<div class="wrapper">
		<div class="unit size2of5" id="source-{$listbuilderId|escape}{if $itemId}-{$itemId|escape}{/if}">
 			<ul>
				<li>
					<span>
						<select name="selectList-{$listbuilderId|escape}" id="selectList-{$listbuilderId|escape}" class="field select">
							<option>{translate key="manager.setup.selectOne"}</option>
							{foreach from=$listbuilder->getPossibleItemList() item=item}{$item}{/foreach}
						</select>
					</span>
				</li>
			</ul>
		</div>
		<div class="unit size1of10 listbuilder_controls">
			<a href="#" id="add-{$listbuilderId|escape}{if $itemId}-{$itemId|escape}{/if}" onclick="return false;" class="add_item"></a>
			<a href="#" id="delete-{$listbuilderId|escape}{if $itemId}-{$itemId|escape}{/if}" onclick="return false;" class="remove_item"></a>
		</div>
		<div id="results-{$listbuilderId|escape}{if $itemId}-{$itemId|escape}{/if}" class="unit size1of2 lastUnit listbuilder_results">
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
	<!--
	{literal}
		addItem("{/literal}{$addUrl|escape:"javascript"}{literal}", "{/literal}{$listbuilderId|escape:"javascript"}{if $itemId}-{$itemId|escape:"javascript"}{/if}{literal}", "{/literal}{$localizedButtons|escape:"javascript"}{literal}");
		deleteItems("{/literal}{$deleteUrl|escape:"javascript"}{literal}", "{/literal}{$listbuilderId|escape:"javascript"}{if $itemId}-{$itemId|escape:"javascript"}{/if}{literal}");
		selectRow("{/literal}{$listbuilderId|escape:"javascript"}{if $itemId}-{$itemId|escape:"javascript"}{/if}{literal}");
	{/literal}
	// -->
	</script>
</div>

