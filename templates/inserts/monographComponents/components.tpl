{**
 * components.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Component listing.
 *
 * $Id$
 *}
{literal}
<script type="text/javascript">
<!--
// Move component author up/down
function moveComponentAuthor(dir, authorIndex, componentIndex) {
	var form = document.submit;
	form.moveComponentAuthor.value = 1;
	form.moveComponentAuthorDir.value = dir;
	form.moveComponentAuthorIndex.value = authorIndex;
	form.moveComponentAuthorComponentIndex.value = componentIndex;
	form.submit();
}
// Move component up/down
function moveComponent(dir, componentIndex) {
	var form = document.submit;
	form.moveComponent.value = 1;
	form.moveComponentDir.value = dir;
	form.moveComponentIndex.value = componentIndex;
	form.submit();
}
// -->
</script>
{/literal}

<input type="hidden" name="moveComponentAuthor" value="0" />
<input type="hidden" name="moveComponentAuthorDir" value="" />
<input type="hidden" name="moveComponentAuthorIndex" value="" />
<input type="hidden" name="moveComponentAuthorComponentIndex" value="0" />
<input type="hidden" name="moveComponent" value="0" />
<input type="hidden" name="moveComponentDir" value="" />
<input type="hidden" name="moveComponentIndex" value="" />

<input type="hidden" name="deletedComponents" value="{$deletedComponents|escape}" />

<div id="components">
<h3>{translate key="inserts.monographComponents.heading.prep"}</h3>
<br />
{translate key="inserts.monographComponents.description"}
<br /><br />

<table width="100%" class="listing">
	<tr>
		<td class="headseparator" colspan="2">&nbsp;</td>
	</tr>

<tr class="heading" valign="bottom">
<td width="50%">{translate key="inserts.monographComponents.heading.info"}</td><td width="50%">{translate key="common.action"}</td>
</tr>
	<tr>
		<td class="headseparator" colspan="2">&nbsp;</td>
	</tr>
{assign var="componentIndex" value=0} 
{foreach name=components from=$components item=component}
<tr>
<td>
	<input type="hidden" name="components[{$componentIndex}][componentId]" value="{$component.componentId|escape}" />
	<input type="hidden" name="components[{$componentIndex}][title][{$formLocale|escape}]" value="{$component.title.$formLocale}" />
	<h4>{$component.title.$formLocale}</h4>
</td>
<td>
	{if $componentIndex > 0}<a href="javascript:moveComponent('u', '{$componentIndex|escape}')" class="action">&uarr;</a>{else}&uarr;{/if} {if $componentIndex < count($components)-1}<a href="javascript:moveComponent('d', '{$componentIndex|escape}')" class="action">&darr;</a>{else}&darr;{/if}
	
	| <input type="submit" name="deleteComponent[{$componentIndex}]" value="{translate key="inserts.monographComponents.button.deleteComponent"}" class="button" />
</td>
</tr>
{assign var="componentAuthorIndex" value=0}
{foreach name=componentAuthors from=$component.authors item=componentAuthor}
<tr>
<td>
	<div class="{if $componentAuthorIndex % 2}evenSideIndicator{else}oddSideIndicator{/if}">
		{assign var="pivotId" value=$componentAuthor.pivotId}
		<input type="hidden" name="components[{$componentIndex}][authors][{$pivotId}]" value="{$pivotId|escape}" />
		<input type="hidden" name="components[{$componentIndex}][authors][{$pivotId}][pivotId]" value="{$pivotId|escape}" />
		&nbsp;<strong>{$contributors[$pivotId].firstName}&nbsp;{$contributors[$pivotId].lastName}</strong>
		<br />
		&nbsp;{$contributors[$pivotId].email}
		<br />
		<input type="radio" name="components[{$componentIndex}][primaryContact]" value="{$pivotId|escape}"{if $component.primaryContact == $pivotId} checked="checked"{/if} /> <label for="components[{$componentIndex|escape}][primaryContact]">{translate key="author.submit.selectPrincipalContact"}</label>
		<br />
		&nbsp;<input type="submit" name="removeComponentAuthor[{$componentIndex}][{$pivotId|escape}]" value="{translate key="inserts.monographComponents.button.removeAuthor"}" class="button" />
	</div>
</td>
<td>
	<a href="javascript:moveComponentAuthor('u', '{$componentAuthorIndex|escape}','{$componentIndex|escape}')" class="action">&uarr;</a> <a href="javascript:moveComponentAuthor('d', '{$componentAuthorIndex|escape}','{$componentIndex|escape}')" class="action">&darr;</a>
</td>
</tr>
{assign var="componentAuthorIndex" value=$componentAuthorIndex+1}
{/foreach}
<tr>
	<td class="separator" colspan="2">&nbsp;</td>
</tr>
{assign var="componentIndex" value=$componentIndex+1}
{foreachelse}
	<tr><td class="nodata" colspan="2">{translate key="common.none"}</em></td></tr>
<tr>
	<td class="separator" colspan="2">&nbsp;</td>
</tr>
{/foreach}
</table>
</div>

{if $scrollToComponents}
{literal}
<script type="text/javascript">
var components = document.getElementById('components');
components.scrollIntoView();
</script>
{/literal}
{/if}

<br />

<div class="newItemContainer">
<h3>{translate key="inserts.monographComponents.heading.newComponent"}</h3>
<p>{translate key="inserts.monographComponents.newComponent.description"}</p>
<table width="100%" class="data">
<tr valign="top">
	<td width="20%" class="label">{translate key="common.title"}</td>
	<td width="80%" class="value"><input type="text" class="textField" name="newComponent[title][{$formLocale|escape}]" size="30" maxlength="255" /></td>
</tr>
{if $workType == WORK_TYPE_EDITED_VOLUME}
<tr valign="top">
	<td width="20%" class="label">{translate key="user.role.authors"}</td>
	<td width="80%">
		<select name="newComponent[authors][]" multiple="multiple" class="selectMenu" size="7" style="width:20em">
			{foreach from=$contributors item=author}
			<option value="{$author.pivotId}">{$author.firstName} {$author.lastName} ({$author.email})</option>
			{/foreach}
		</select>
	</td>
</tr>
{/if}
<tr valign="top">
	<td width="20%"></td>
	<td width="80%"><input type="submit" class="button" name="addComponent" value="{translate key="inserts.monographComponents.button.addComponent"}" /></td>
</tr>
</table>
</div>
 