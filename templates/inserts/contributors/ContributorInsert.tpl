{**
 * ContributorInsert.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Contributor listing.
 *
 * $Id$
 *}
{literal}
<script type="text/javascript">
<!--
// Move contributor up/down
function moveContributor(dir, contributorIndex) {
	var form = document.submit;
	form.moveContributor.value = 1;
	form.moveContributorDir.value = dir;
	form.moveContributorIndex.value = contributorIndex;
	form.submit();
}
// Show/hide element
function show(id) {
	var info = document.getElementById(id);
	if(info.style.display=='block') info.style.display='none';
	else info.style.display='block';
}
//-->
</script>
{/literal}

<input type="hidden" name="moveContributor" value="0" />
<input type="hidden" name="moveContributorDir" value="" />
<input type="hidden" name="moveContributorIndex" value="" />

<input type="hidden" name="deletedContributors" value="{$deletedContributors|escape}" />

<div id="contributors">
<h3>{translate key="monograph.contributors"}</h3>

<p>{translate key="inserts.contributors.description"}</p>

<table width="100%" class="listing">
<tr>
	<td class="headseparator" colspan="2">&nbsp;</td>
</tr>

<tr class="heading" valign="bottom">
<td width="50%">{translate key="inserts.contributors.heading.info"}</td><td width="50%">{translate key="common.action"}</td>
</tr>
<tr>
	<td class="headseparator" colspan="2">&nbsp;</td>
</tr>
{assign var="firstOther" value=true}
{assign var="authorArrayIndex" value=0}

{foreach name=authors from=$contributors item=author}
{if $workType == WORK_TYPE_EDITED_VOLUME and $authorArrayIndex == 0}
<tr>
	<td colspan="2"><h4>{translate key="inserts.contributors.volumeEditors"}</h4></td>
</tr>
<tr>
	<td class="separator" colspan="2">&nbsp;</td>
</tr>
{/if}
{if $workType == WORK_TYPE_EDITED_VOLUME and $authorArrayIndex == 0 and $author.contributionType != CONTRIBUTION_TYPE_VOLUME_EDITOR}
<tr>
	<td class="nodata" colspan="2">{translate key="common.none"}</em></td>
</tr>
<tr>
	<td class="separator" colspan="2">&nbsp;</td>
</tr>
{/if}
{if $workType == WORK_TYPE_EDITED_VOLUME and $firstOther and $author.contributionType != CONTRIBUTION_TYPE_VOLUME_EDITOR}
<tr>
	<td colspan="2"><h4>{translate key="inserts.contributors.otherContributors"}</h4></td>
</tr>
<tr>
	<td class="separator" colspan="2">&nbsp;</td>
</tr>
{assign var="firstOther" value=false}
{/if}
{assign var="authorIndex" value=$author.pivotId}
<tr>
	<td>
		<input type="hidden" name="contributors[{$authorIndex|escape}][pivotId]" value="{$author.pivotId|escape}" />
		<input type="hidden" name="contributors[{$authorIndex|escape}][contributorId]" value="{$author.contributorId|escape}" />
		<strong>{$author.firstName}&nbsp;{$author.lastName}</strong>
		<br />
		<em>{$author.email}</em>
		<br />
		{if $workType != WORK_TYPE_EDITED_VOLUME or ($workType == WORK_TYPE_EDITED_VOLUME and $author.contributionType == CONTRIBUTION_TYPE_VOLUME_EDITOR)}
		<input type="radio" name="primaryContact" value="{$authorIndex|escape}"{if $primaryContact == $authorIndex} checked="checked"{/if} /> <label for="primaryContact">{translate key="submission.submit.selectPrincipalContact"}</label>
		<br />
		{/if}
		<a href="javascript:show('authors-{$authorIndex|escape}-display')">{translate key="inserts.contributors.details"}</a>
	</td>
	<td>
		{if $workType != WORK_TYPE_EDITED_VOLUME or ($workType == WORK_TYPE_EDITED_VOLUME and $author.contributionType == CONTRIBUTION_TYPE_VOLUME_EDITOR)}
		<a href="javascript:moveContributor('u', '{$authorArrayIndex|escape}')" class="action">&uarr;</a>
		<a href="javascript:moveContributor('d', '{$authorArrayIndex|escape}')" class="action">&darr;</a>
		| {/if}<input type="submit" name="deleteContributor[{$authorIndex|escape}]" value="{translate key="common.delete"}" class="button" />
	</td>
</tr>
<tr>
<td colspan="2">
	<div id="authors-{$authorIndex|escape}-display" style="display:none" class="{if $authorIndex % 2}evenSideIndicator{else}oddSideIndicator{/if}">
		<table class="data">
			<tr valign="top">
				<td width="20%" class="label">{fieldLabel name="authors-$authorIndex-firstName" required="true" key="user.firstName"}</td>
				<td width="80%" class="value"><input type="text" class="textField" name="contributors[{$authorIndex|escape}][firstName]" id="authors-{$authorIndex|escape}-firstName" value="{$author.firstName|escape}" size="20" maxlength="40" /></td>
			</tr>
			<tr valign="top">
				<td width="20%" class="label">{fieldLabel name="authors-$authorIndex-middleName" key="user.middleName"}</td>
				<td width="80%" class="value"><input type="text" class="textField" name="contributors[{$authorIndex|escape}][middleName]" id="authors-{$authorIndex|escape}-middleName" value="{$author.middleName|escape}" size="20" maxlength="90" /></td>
			</tr>
			<tr valign="top">
				<td width="20%" class="label">{fieldLabel name="authors-$authorIndex-lastName" required="true" key="user.lastName"}</td>
				<td width="80%" class="value"><input type="text" class="textField" name="contributors[{$authorIndex|escape}][lastName]" id="authors-{$authorIndex|escape}-lastName" value="{$author.lastName|escape}" size="20" maxlength="90" /></td>
			</tr>
			<tr valign="top">
				<td width="20%" class="label">{fieldLabel name="authors-$authorIndex-affiliation" key="user.affiliation"}</td>
				<td width="80%" class="value"><textarea class="textField" name="contributors[{$authorIndex|escape}][affiliation]" id="authors-{$authorIndex|escape}-affiliation" rows="5" cols="40">{$author.affiliation|escape}</textarea></td>
			</tr>
			<tr valign="top">
				<td width="20%" class="label">{fieldLabel name="authors-$authorIndex-country" key="common.country"}</td>
				<td width="80%" class="value">
					<select name="contributors[{$authorIndex}][country]" class="selectMenu">
						<option value=""></option>
						{html_options options=$countries selected=$author.country|escape}
					</select>
				</td>
			</tr>
			<tr valign="top">
				<td width="20%" class="label">{fieldLabel name="authors-$authorIndex-email" required="true" key="user.email"}</td>
				<td width="80%" class="value"><input type="text" class="textField" name="contributors[{$authorIndex|escape}][email]" id="authors-{$authorIndex|escape}-email" value="{$author.email|escape}" size="30" maxlength="90" /></td>
			</tr>
			<tr valign="top">
				<td width="20%" class="label">{fieldLabel name="authors-$authorIndex-url" key="user.url"}</td>
				<td width="80%" class="value"><input type="text" class="textField" name="contributors[{$authorIndex|escape}][url]" id="authors-{$authorIndex|escape}-url" value="{$author.url|escape}" size="30" maxlength="90" /></td>
			</tr>
			<tr valign="top">
				<td class="label">{fieldLabel name="biography" key="user.biography"}<br />{translate key="user.biography.description"}</td>
				<td width="80%" class="value" colspan="2"><textarea name="contributors[{$authorIndex|escape}][biography][{$formLocale|escape}]" class="textArea">{$author.biography.$formLocale}</textarea>
				</td>
			</tr>
			{if $workType == WORK_TYPE_EDITED_VOLUME}
			<tr valign="top">
				<td width="80%" class="value" colspan="2">
					<input type="checkbox" name="contributors[{$authorIndex|escape}][contributionType]" id="authors-{$authorIndex}-contributionType" value="1"{if $author.contributionType == CONTRIBUTION_TYPE_VOLUME_EDITOR} checked="checked"{/if} /> <label for="authors-{$authorIndex}-contributionType">{translate key="inserts.contributors.isVolumeEditor"}</label>
				</td>
			</tr>
			{/if}
			<tr valign="top">
				<td width="80%" class="value" colspan="2"><input type="submit" class="button" name="updateContributorInfo[{$authorIndex|escape}]" value="Update Contributor Information" /></td>
			</tr>
			<tr>
				<td colspan="2"><br/></td>
			</tr>
		</table>
	</div>
</td>
</tr>
<tr>
	<td class="separator" colspan="2">&nbsp;</td>
</tr>
{assign var="authorArrayIndex" value=$authorArrayIndex+1}
{foreachelse}
<tr>
	<td class="nodata" colspan="2">{translate key="common.none"}</em></td>
</tr>
<tr>
	<td class="separator" colspan="2">&nbsp;</td>
</tr>
{/foreach}
</table>
</div>

{if $scrollToAuthor}
{literal}
	<script type="text/javascript">
		var contributors = document.getElementById('contributors');
		contributors.scrollIntoView();
	</script>
{/literal}
{/if}

<br />

{include file="inserts/contributors/NewContributorForm.tpl"}
