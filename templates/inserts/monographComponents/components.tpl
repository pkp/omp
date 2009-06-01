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

<h3>{translate key="inserts.monographComponents.heading.prep"}</h3>
<br />
{translate key="monograph.component.description"}
<br /><br />
{assign var="componentIndex" value=0} 

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
{foreach name=components from=$components item=component}
{if !$component.deleted}
<tr>
<td>
	<input type="hidden" name="components[{$componentIndex}][title][{$formLocale|escape}]" value="{$component.title.$formLocale}" />
	<h4>{$component.title.$formLocale}</h4>
</td>
<td>
	{if $componentIndex > 0}<a href="javascript:moveComponent('u', '{$componentIndex|escape}')" class="action">&uarr;</a>{else}&uarr;{/if} {if $componentIndex < count($components)-1}<a href="javascript:moveComponent('d', '{$componentIndex|escape}')" class="action">&darr;</a>{else}&darr;{/if}
	
	| <input type="submit" name="deleteComponent[{$componentIndex}][{$componentAuthorIndex}]" value="{translate key="inserts.monographComponents.button.deleteComponent"}" class="button" />
</td>
</tr>
{if count($component.authors) > 0}
{foreach name=componentAuthors from=$component.authors key=componentAuthorIndex item=componentAuthor}
{if !$componentAuthor.removed}
<tr>
<td>
	<div style="border-left:2px solid {if $componentAuthorIndex % 2}#D0D0D0{else}#e5aa5c{/if}">
		<input type="hidden" name="components[{$componentIndex|escape}][authors][{$componentAuthorIndex|escape}][authorId]" value="{$componentAuthor.authorId}" />
		{assign var="authorId" value=$componentAuthor.authorId}
		&nbsp;<strong>{$contributors[$authorId].firstName}&nbsp;{$contributors[$authorId].lastName}</strong>
		<br />
		&nbsp;{$contributors[$authorId].email}
		<br />
		<input type="radio" name="components[{$componentIndex|escape}][primaryContact]" value="{$componentAuthorIndex|escape}"{if $components[$componentIndex][primaryContact] == $authorIndex} checked="checked"{/if} /> <label for="components[{$componentIndex|escape}][primaryContact]">{translate key="author.submit.selectPrincipalContact"}</label>
		<br />
		&nbsp;<input type="submit" name="removeComponentAuthor[{$componentIndex}][{$componentAuthorIndex}]" value="{translate key="inserts.monographComponents.button.removeAuthor"}" class="button" />
	</div>
</td>
<td>
	<div style="height:100%;border-left:2px solid {if $componentAuthorIndex % 2}#D0D0D0{else}#e5aa5c{/if}">
	&nbsp;<a href="javascript:moveComponentAuthor('u', '{$componentAuthorIndex|escape}','{$componentIndex|escape}')" class="action">&uarr;</a> <a href="javascript:moveComponentAuthor('d', '{$componentAuthorIndex|escape}','{$componentIndex|escape}')" class="action">&darr;</a>

	</div>
</td>
</tr>
{/if}
{/foreach}
{/if}
{assign var="componentIndex" value=$componentIndex+1}
<tr>
	<td class="separator" colspan="2">&nbsp;</td>
</tr>
{/if}
{foreachelse}
	<tr><td class="nodata" colspan="2">{translate key="common.none"}</em></td></tr>
<tr>
	<td class="separator" colspan="2">&nbsp;</td>
</tr>

{/foreach}
</table>

<br />

<div style="border:1px solid #e5aa5c;background-color:#ffd9a7;margin-left:10%;margin-right:10%">
<table style="info">
<tr>
	<td width="10%"></td><td width="80%"><h2>{translate key="inserts.monographComponents.heading.newComponent"}</h2></td><td width="10%"></td>
</tr>
<tr>
	<td width="10%"></td>
	<td width="80%">
<table width="100%" class="data">
<tr valign="top">
	<td width="20%" class="label">{translate key="common.title"}</td>
	<td width="80%" class="value"><input type="text" class="textField" name="newComponent[title][{$formLocale|escape}]" size="30" maxlength="255" /></td>
</tr>
{if $workType == EDITED_VOLUME}
<tr valign="top">
	<td width="20%" class="label">{translate key="monograph.component.authors"}</td>
	<td width="80%">
		<select name="newComponent[authors][]" multiple="multiple" class="selectMenu" size="7" style="width:20em">
			{foreach from=$contributors item=author}
			{if !$author.deleted}
			<option value="{$author.authorId}">{$author.firstName} {$author.lastName} ({$author.email})</option>
			{/if}
			{/foreach}
		</select>
	</td>
</tr>
{/if}
<tr valign="top">
	<td width="20%"></td>
	<td width="80%"><p><input type="submit" class="button" name="addComponent" value="{translate key="inserts.monographComponents.button.addComponent"}" /></p></td>
</tr>
</table>
	</td>
	<td width="10%"></td>
</tr>
</table>

</div>
 