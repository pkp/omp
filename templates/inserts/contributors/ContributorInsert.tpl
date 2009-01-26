
{literal}
<script type="text/javascript">
<!--
// Move author up/down
function moveComponentAuthor(dir, authorIndex,componentIndex) {
	var form = document.submit;
	form.moveComponentAuthor.value = 1;
	form.moveAuthorDir.value = dir;
	form.moveAuthorIndex.value = authorIndex;
	form.moveAuthorComponent.value = componentIndex;
	form.submit();
}
// -->
</script>
{/literal}
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
	if(info.style.display=='block') info.style.display='none';
	else info.style.display='block';
}

//document onready
function attachCountryList(id,uc) {
	var c = document.getElementById(id);
	var html = '';
	{/literal}
	{foreach from=$countries item=country}
	//	html+="<option value=x>{*$country*}</option>";
	{/foreach}
	{literal};
	c.innerHTML= html;
}
//-->
</script>
{/literal}

<h3>{translate key="monograph.contributors"}</h3>
{assign var="authorIndex" value=0} 

<div style="border:1px solid #E0E0E0">
{foreach name=authors from=$contributors item=author}

	<input type="hidden" name="contributors[{$author.authorId|escape}][authorId]" value="{$author.authorId|escape}" />
	<input type="hidden" name="contributors[{$author.authorId|escape}][deleted]" value="0" />

	{if 1}{*!$author.deleted*}
		{assign var="authorIndex" value=$authorIndex+1}

		<div style="background-color:{if $authorIndex % 2}#FFFFFF{else}#E0E0E0{/if}">
		{if $monographType == EDITED_VOLUME}
			<a style="text-decoration:none" href="javascript:show('authors-{$author.authorId|escape}-display')">(+) {if $author.contributionType == VOLUME_EDITOR}<strong>Volume Editor: </strong>{/if}{$author.firstName} {$author.lastName}</a>
			{if $author.contributionType == VOLUME_EDITOR}<input type="radio" name="primaryContact" value="{$author.authorId}" {if $primaryContact == $author.authorId}checked="checked" {/if}/>{/if}
		{else}
			<a style="text-decoration:none" href="javascript:show('authors-{$author.authorId|escape}-display')">(+) {$author.firstName}&nbsp;{$author.lastName}</a>
			<input type="radio" name="primaryContact" value="{$author.authorId}" {if $primaryContact == $author.authorId}checked="checked" {/if}/>
		{/if}
		<br />
		</div>

		<div id="authors-{$author.authorId|escape}-display" style="display:none;border-left:1px solid black;padding-left:10px;background-color:{if $authorIndex % 2}#FFFFFF{else}#E0E0E0{/if}">
			{assign var="authorId" value=$author.authorId}
			<table width="100%" class="data">
				  <tr valign="top">
					  <td width="20%" class="label">{fieldLabel name="authors-$authorId-firstName" required="true" key="user.firstName"}</td>
					  <td width="80%" class="value"><input type="text" class="textField" name="contributors[{$author.authorId|escape}][firstName]" id="authors-{$author.authorId|escape}-firstName" value="{$author.firstName|escape}" size="20" maxlength="40" /></td>
				  </tr>
				  <tr valign="top">
					  <td width="20%" class="label">{fieldLabel name="authors-$authorId-middleName" key="user.middleName"}</td>
					  <td width="80%" class="value"><input type="text" class="textField" name="contributors[{$author.authorId|escape}][middleName]" id="authors-{$author.authorId|escape}-middleName" value="{$author.middleName|escape}" size="20" maxlength="90" /></td>
				  </tr>
				  <tr valign="top">
					  <td width="20%" class="label">{fieldLabel name="authors-$authorId-lastName" required="true" key="user.lastName"}</td>
					  <td width="80%" class="value"><input type="text" class="textField" name="contributors[{$author.authorId|escape}][lastName]" id="authors-{$author.authorId|escape}-lastName" value="{$author.lastName|escape}" size="20" maxlength="90" /></td>
				  </tr>
				  <tr valign="top">
					  <td width="20%" class="label">{fieldLabel name="authors-$authorId-affiliation" key="user.affiliation"}</td>
					  <td width="80%" class="value"><input type="text" class="textField" name="contributors[{$author.authorId|escape}][affiliation]" id="authors-{$author.authorId|escape}-affiliation" value="{$author.affiliation|escape}" size="30" maxlength="90" /></td>
				  </tr>
				  <tr valign="top">
					  <td width="20%" class="label">{fieldLabel name="authors-$authorId-country" key="common.country"}</td>
					  <td width="80%" class="value">
						<select name="contributors[{$author.authorId}][country]" class="selectMenu">
							<option value=""></option>
							{html_options options=$countries selected=$author.country|escape}
						</select>
					</td>
				  </tr>
				  <tr valign="top">
					  <td width="20%" class="label">{fieldLabel name="authors-$authorId-email" required="true" key="user.email"}</td>
					  <td width="80%" class="value"><input type="text" class="textField" name="contributors[{$author.authorId|escape}][email]" id="authors-{$author.authorId|escape}-email" value="{$author.email|escape}" size="30" maxlength="90" /></td>
				  </tr>
				  <tr valign="top">
					  <td width="20%" class="label">{fieldLabel name="authors-$authorId-url" key="user.url"}</td>
					  <td width="80%" class="value"><input type="text" class="textField" name="contributors[{$author.authorId|escape}][url]" id="authors-{$author.authorId|escape}-url" value="{$author.url|escape}" size="30" maxlength="90" /></td>
				  </tr>
				<tr valign="top">
					<td class="label">{fieldLabel name="biography" key="user.biography"}<br />{translate key="user.biography.description"}</td>
					<td width="80%" class="value" colspan="2"><textarea name="contributors[{$author.authorId|escape}][biography][{$formLocale|escape}]" class="textArea">{$author.biography.$formLocale}</textarea>
					</td>
				</tr>
			{if 1}<!--$smarty.foreach.authors.total > 1-->
				<tr valign="top">
					<td width="80%" class="value" colspan="2"><input type="checkbox" name="contributors[{$author.authorId|escape}][contributionType]" id="authors-{$author.authorId}-contributionType" value="1"{if VOLUME_EDITOR == $author.contributionType} checked="checked"{/if} /> <label for="authors-{$author.authorId}-contributionType">This contributor is a volume editor.</label>
				</td>
				</tr>
			<!--	<tr valign="top">
					<td width="80%" class="value" colspan="2"><input type="submit" name="deleteAuthor[{$author.getAuthodId}]" value="{translate key="author.submit.deleteAuthor"}" class="button" /></td>
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

{include file="inserts/contributors/NewContributorForm.tpl"}

<input type="hidden" name="newAuthor[deleted]" value="0" />
</div> 

