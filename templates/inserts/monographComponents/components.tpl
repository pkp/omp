{literal}
<script type="text/javascript">
<!--
// Move author up/down

// Move author up/down
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






<h3>Monograph Component Preparation</h3>
<input type="hidden" name="moveAuthor" value="0" />
<input type="hidden" name="moveAuthorDir" value="" />
<input type="hidden" name="moveAuthorIndex" value="" />
<input type="hidden" name="moveComponent" value="0" />
<input type="hidden" name="moveComponentDir" value="" />
<input type="hidden" name="moveComponentIndex" value="" />
{assign var="componentIndex" value=0} 
<div style="border:1px solid #E0E0E0">
{foreach name=components from=$components item=component}
{assign var="componentIndex" value=$componentIndex+1}

<div style="background-color:{if $componentIndex % 2}#FFFFFF{else}#E0E0E0{/if}">
	<a style="text-decoration:none" href="javascript:show('components-{$componentIndex}-authors')">(+) {$component.title}</a>
	{if $smarty.foreach.components.total > 1 }
	{if $componentIndex > 1}<a href="javascript:moveComponent('u', '{$componentIndex|escape}')" class="action">&uarr;</a>{else}&uarr;{/if} {if $componentIndex < count($components)}<a href="javascript:moveComponent('d', '{$componentIndex|escape}')" class="action">&darr;</a>{else}&darr;{/if}
	{/if}
	<br />
</div>

<div id="components-{$componentIndex}-authors" style="display:none;border-left:1px solid black;padding-left:10px;background-color:{if $componentIndex % 2}#FFFFFF{else}#E0E0E0{/if}">
	<input type="hidden" name="components[{$componentIndex}][title]" value="{$component.title}" />
	{if count($component.authors) > 0}
		<h2>Component Authors</h2>
		<table border="1">
		<tr>
		<td></td><td>Author</td><td>Email Address</td><td>Primary Contact</td>
		</tr>

	
	{foreach name=componentAuthors from=$component.authors key=componentAuthorIndex item=componentAuthor}
		<input type="hidden" name="components[{$componentIndex|escape}][authors][{$componentAuthorIndex|escape}][authorId]" value="{$componentAuthor.authorId}" />
		{assign var="authorId" value=$componentAuthor.authorId}
		<tr>
		<td>
			<a href="javascript:moveComponentAuthor('u', '{$componentAuthorIndex|escape}','{$componentIndex|escape}')" class="action">&uarr;</a> <a href="javascript:moveComponentAuthor('d', '{$componentIndex|escape}','{$componentIndex|escape}')" class="action">&darr;</a>
		</td>
		<td>{$authors[$authorId].firstName}&nbsp;{$authors[$authorId].lastName}</td>
		<td>{$authors[$authorId].email}</td>
		<td><input type="radio" name="components[{$componentIndex|escape}][primaryContact]"{if $component.primaryContact == $authorId} checked="checked"{/if}" value="{$authorId}"/></td>
		</tr>
	{/foreach}
		</table>
	{else}
			<em>There are no authors currently associated with this component</em>
	{/if}
	<br />

</div>

{foreachelse}
	<em>There are no components currently associated with this manuscript.</em>
{/foreach}
</div>


<br />

<a style="text-decoration:none" href="javascript:show('showNewComponent')">(+) Add Component</a>
<div id="showNewComponent" style="display:none;border:1px solid #e5aa5c;background-color:#ffd9a7">
<table width="100%" class="data">
<tr valign="top">
	<td width="20%" class="label">Component Title</td>
	<td width="80%" class="value"><input type="text" class="textField" name="newComponent[title]" size="40" maxlength="255" /></td>
</tr>
<tr valign="top">
	<td width="20%" class="label">Component Authors</td>
	<td width="80%">
		<select name="newComponent[authors][]" multiple="multiple" class="selectMenu" size="7" style="width:20em">
			{foreach from=$authors item=author}
			{if !$author.deleted}
			<option value="{$author.authorId}">{$author.firstName} {$author.lastName} ({$author.email})</option>
			{/if}
			{/foreach}
		</select>
	</td>
</tr>
</table>

<p><input type="submit" class="button" name="addComponent" value="{translate key="author.submit.addComponent"}" /></p>
</div>
 