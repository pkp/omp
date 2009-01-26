{literal}
<script type="text/javascript">
<!--
// Move author up/down
function moveAuthor(dir, authorIndex) {
	var form = document.submit;
	form.moveAuthor.value = 1;
	form.moveAuthorDir.value = dir;
	form.moveAuthorIndex.value = authorIndex;
	form.submit();
}

function show(id) {
	var info = document.getElementById(id);
	if(info.style.display=='block')
		info.style.display='none';
	else
		info.style.display='block';
}

</script>
{/literal}

<h3>Monograph Contributors</h3>
{assign var="authorIndex" value=0} 

<div style="border:1px solid #E0E0E0">
{$isEditedVolume}
{foreach name=authors from=$authors item=author}
{$isEditedVolume}
	<input type="hidden" name="authors[{$author->getAuthorId()|escape}][authorId]" value="{$author->getAuthorId()|escape}" />
	<input type="hidden" name="authors[{$author->getAuthorId()|escape}][deleted]" value="{*$author.deleted*}" />

	{if 1}{*!$authordeleted*}
		{assign var="authorIndex" value=$authorIndex+1}
		<div style="background-color:{if $authorIndex % 2}#FFFFFF{else}#E0E0E0{/if}">
			<a style="text-decoration:none" href="javascript:show('authors-{$author->getAuthorId()|escape}-display')">(+) {if $author->getVolumeEditor()}<strong>Volume Editor: </strong>{/if}{$author->getFullName()}</a>
			{if $author->getVolumeEditor()}<input type="radio" name="primaryContact" value="{$author->getAuthorId()}" {if $primaryContact == $author->getAuthorId()}checked="checked" {/if}/>{/if}
			<br />
		</div>
		<div id="authors-{$author->getAuthorId()|escape}-display" style="display:none;border-left:1px solid black;padding-left:10px;background-color:{if $authorIndex % 2}#FFFFFF{else}#E0E0E0{/if}">
			{assign var="authorId" value=$author->getAuthorId()}
			<table width="100%" class="data">
				  <tr valign="top">
					  <td width="20%" class="label">{translate key="user.firstName"}</td>
					  <td width="80%" class="value">{$author->getFirstName()|escape}</td>
				  </tr>
				  <tr valign="top">
					  <td width="20%" class="label">{translate key="user.middleName"}</td>
					  <td width="80%" class="value">{$author->getMiddleName()|escape|default:"&mdash;"}</td>
				  </tr>
				  <tr valign="top">
					  <td width="20%" class="label">{translate key="user.lastName"}</td>
					  <td width="80%" class="value">{$author->getLastName()|escape}</td>
				  </tr>
				  <tr valign="top">
					  <td width="20%" class="label">{translate key="user.affiliation"}</td>
					  <td width="80%" class="value">{$author->getAffiliation()|escape|default:"&mdash;"}</td>
				  </tr>
				  <tr valign="top">
					  <td width="20%" class="label">{translate key="common.country"}</td>
					  <td width="80%" class="value">{$author->getCountryLocalized()|escape|default:"&mdash;"}</td>
				  </tr>
				  <tr valign="top">
					  <td width="20%" class="label">{translate key="user.email"}</td>
					  <td width="80%" class="value">{$author->getEmail()|escape}</td>
				  </tr>
				  <tr valign="top">
					  <td class="label">{translate key="user.url"}</td>
					  <td class="value">{$author->getUrl()|escape|default:"&mdash;"}</td>
				  </tr>
				  <tr valign="top">
					  <td class="label">{translate key="user.biography"}</td>
					  <td class="value">{$author->getAuthorBiography()|strip_unsafe_html|nl2br|default:"&mdash;"}</td>
				  </tr>
			{if 1}<!--$smarty.foreach.authors.total > 1-->
				<tr valign="top">
					<td width="80%" class="value" colspan="2"><input type="checkbox" name="authors[{$author->getAuthorId()|escape}][isVolumeEditor]" id="authors-{$author->getAuthorId()}-isVolumeEditor" value="1"{if 1 == $author->getVolumeEditor()} checked="checked"{/if} /> <label for="authors-{$author->getAuthorId()}-isVolumeEditor">This contributor is a volume editor.</label>
				</td>
				</tr>
			<!--	<tr valign="top">
					<td width="80%" class="value" colspan="2"><input type="submit" name="deleteAuthor[{$author->getAuthorId()}]" value="{translate key="author.submit.deleteAuthor"}" class="button" /></td>
				</tr>-->
				<tr valign="top">
					<td width="80%" class="value" colspan="2"><a href="{url op="viewAuthorMetadata" path="$authorId"}">EDIT</a></td>
				</tr>
				<tr>
					<td colspan="2"><br/></td>
				</tr>
			{/if}
			</table>
		</div>
	{/if}

{foreachelse}
	<input type="hidden" name="primaryContact" value="0" />
	<em>There are currently no contributors associated with this work.</em>
{/foreach}
</div>


<br />
<a style="text-decoration:none" href="javascript:show('showNewAuthor')">(+) Add an author</a>
 <div id="showNewAuthor" style="display:none;border:1px solid #e5aa5c;background-color:#ffd9a7">

<input type="hidden" name="newAuthor[authorId]" value="{$authors|@count}" />

{


<table width="100%" class="data">
<tr valign="top">
	<td width="20%" class="label">{fieldLabel name="newAuthor-firstName" required="true" key="user.firstName"}</td>
	<td width="80%" class="value"><input type="text" class="textField" name="author[firstName]" value="{$newAuthor.firstName|escape}" id="newAuthors-firstName" size="20" maxlength="40" /></td>
</tr>
<tr valign="top">
	<td width="20%" class="label">{fieldLabel name="newAuthors-lastName" required="true" key="user.lastName"}</td>
	<td width="80%" class="value"><input type="text" class="textField" name="author[lastName]" value="{$newAuthor.lastName|escape}" id="newAuthors-lastName" size="20" maxlength="90" /></td>
</tr>
<tr valign="top">
	<td width="20%" class="label">{fieldLabel name="newAuthors-email" required="true" key="user.email"}</td>
	<td width="80%" class="value"><input type="text" class="textField" name="author[email]" value="{$newAuthor.email|escape}" id="newAuthors-email" size="30" maxlength="90" /></td>
</tr>
</table>
<p><input type="submit" class="button" name="addAuthor" value="{translate key="author.submit.addAuthor"}" /></p>
</div> 