{**
 * productionAssignment.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Production assignment form.
 *
 * $Id$
 *}
{strip}
{translate|assign:"pageTitleTranslated" key="production.assignment"" id=$submission->getId()}
{assign var="pageCrumbTitle" value="production.assignment"}
{include file="common/header.tpl"}
{/strip} 


<form action="{url op="productionAssignment" path=$submission->getId()|to_array:$assignmentId}" method="post">
<input type="hidden" name="fromDesignAssignmentForm" value="1" />
<h3>{translate key="production.assignment.new"}</h3>
<p>{translate key="production.assignment.description"}</p>
<table class="data">
<tr valign="top">
	<td class="label">{translate key="common.type"}</td>
	<td class="value">
		<select name="type">
		<option>{translate key="common.select"}</option>
		{html_options_translate options=$assignmentTypeOptions selected=$type}
		</select>
	</td>
</tr>
<tr valign="top">
	<td class="label">{translate key="common.label"}</td>
	<td class="value">
		<input type="text" name="label" value="{$label}	" />
		<br />
		{translate key="submission.layout.galleyLabelInstructions"}
	</td>
</tr>
<tr valign="top">
	<td>&nbsp;</td>
	<td><input type="submit" name="save" value="{translate key="common.add"}" class="button" /></td>
</tr>
</table>
</form>

{include file="common/footer.tpl"}