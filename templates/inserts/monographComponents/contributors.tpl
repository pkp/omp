

<h3>Monograph Contributors</h3>
{assign var="authorIndex" value=0} 

<div style="border:1px solid #E0E0E0">
{$isEditedVolume}
{foreach name=authors from=$authors item=author}
{$isEditedVolume}
	<input type="hidden" name="authors[{$author.authorId|escape}][authorId]" value="{$author.authorId|escape}" />
	<input type="hidden" name="authors[{$author.authorId|escape}][deleted]" value="{$author.deleted}" />

	{if !$author.deleted}
		{assign var="authorIndex" value=$authorIndex+1}
		<div style="background-color:{if $authorIndex % 2}#FFFFFF{else}#E0E0E0{/if}">
			<a style="text-decoration:none" href="javascript:show('authors-{$author.authorId|escape}-display')">(+) {if $author.isVolumeEditor}<strong>Volume Editor: </strong>{/if}{$author.firstName} {$author.lastName}</a>
			{if $author.isVolumeEditor}<input type="radio" name="primaryContact" value="{$author.authorId}" {if $primaryContact == $author.authorId}checked="checked" {/if}/>{/if}
			<br />
		</div>
		<div id="authors-{$author.authorId|escape}-display" style="display:none;border-left:1px solid black;padding-left:10px;background-color:{if $authorIndex % 2}#FFFFFF{else}#E0E0E0{/if}">
			{assign var="authorId" value=$author.authorId}
			<table width="100%" class="data">
				  <tr valign="top">
					  <td width="20%" class="label">{fieldLabel name="authors-$authorId-firstName" required="true" key="user.firstName"}</td>
					  <td width="80%" class="value"><input type="text" class="textField" name="authors[{$author.authorId|escape}][firstName]" id="authors-{$author.authorId|escape}-firstName" value="{$author.firstName|escape}" size="20" maxlength="40" /></td>
				  </tr>
				  <tr valign="top">
					  <td width="20%" class="label">{fieldLabel name="authors-$authorId-lastName" required="true" key="user.lastName"}</td>
					  <td width="80%" class="value"><input type="text" class="textField" name="authors[{$author.authorId|escape}][lastName]" id="authors-{$author.authorId|escape}-lastName" value="{$author.lastName|escape}" size="20" maxlength="90" /></td>
				  </tr>
				  <tr valign="top">
					  <td width="20%" class="label">{fieldLabel name="authors-$authorId-email" required="true" key="user.email"}</td>
					  <td width="80%" class="value"><input type="text" class="textField" name="authors[{$author.authorId|escape}][email]" id="authors-{$author.authorId|escape}-email" value="{$author.email|escape}" size="30" maxlength="90" /></td>
				  </tr>
			{if 1}<!--$smarty.foreach.authors.total > 1-->
				<tr valign="top">
					<td width="80%" class="value" colspan="2"><input type="checkbox" name="authors[{$author.authorId|escape}][isVolumeEditor]" id="authors-{$author.authorId}-isVolumeEditor" value="1"{if 1 == $author.isVolumeEditor} checked="checked"{/if} /> <label for="authors-{$author.authorId}-isVolumeEditor">This contributor is a volume editor.</label>
				</td>
				</tr>
			<!--	<tr valign="top">
					<td width="80%" class="value" colspan="2"><input type="submit" name="deleteAuthor[{$author.authorId}]" value="{translate key="author.submit.deleteAuthor"}" class="button" /></td>
				</tr>-->
				<tr valign="top">
					<td width="80%" class="value" colspan="2"><input type="submit" class="button" name="updateContributorInfo[{$author.authorId|escape}]" value="Update Contributor Information" /></td>
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

{if $cannotAddAuthor}
<p>Please fill in all of the required fields!</p>
{/if}
<table width="100%" class="data">
<tr valign="top">
	<td width="20%" class="label">{fieldLabel name="newAuthor-firstName" required="true" key="user.firstName"}</td>
	<td width="80%" class="value"><input type="text" class="textField" name="newAuthor[firstName]" value="{$newAuthor.firstName|escape}" id="newAuthors-firstName" size="20" maxlength="40" /></td>
</tr>
<tr valign="top">
	<td width="20%" class="label">{fieldLabel name="newAuthors-lastName" required="true" key="user.lastName"}</td>
	<td width="80%" class="value"><input type="text" class="textField" name="newAuthor[lastName]" value="{$newAuthor.lastName|escape}" id="newAuthors-lastName" size="20" maxlength="90" /></td>
</tr>
<tr valign="top">
	<td width="20%" class="label">{fieldLabel name="newAuthors-email" required="true" key="user.email"}</td>
	<td width="80%" class="value"><input type="text" class="textField" name="newAuthor[email]" value="{$newAuthor.email|escape}" id="newAuthors-email" size="30" maxlength="90" /></td>
</tr>
<tr valign="top">
	<td width="80%" class="value" colspan="2"><input type="checkbox" id="newAuthors-isVolumeEditor" name="newAuthor[isVolumeEditor]" {if 1 == $newAuthor.isVolumeEditor}checked="checked"{/if}/> <label for="newAuthors-isVolumeEditor">This contributor is a volume editor.</label> </td>
</tr>
</table>
<input type="hidden" name="newAuthor[deleted]" value="0" />
<p><input type="submit" class="button" name="addAuthor" value="{translate key="author.submit.addAuthor"}" /></p>
</div> 

