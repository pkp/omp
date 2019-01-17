{**
 * plugins/importexport/native/templates/results.tpl
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Result of operations this plugin performed
 *}
{if $submissionsWarnings}
	<h2>{translate key="plugins.importexport.common.warningsEncountered"}</h2>
	{foreach from=$submissionsWarnings item=submissionsWarningMessages name=submissionsWarnings}
		{if $submissionsWarningMessages|@count > 0}
			<p>$smarty.foreach.submissionsWarnings.iteration}. {translate key="submissions.submission"}</p>
			<ul>
				{foreach from=$submissionsWarningMessages item=submissionsWarningMessage}
					<li>{$submissionsWarningMessage|escape}</li>
				{/foreach}
			</ul>
		{/if}
	{/foreach}
{/if}
{if $validationErrors}
	<h2>{translate key="plugins.importexport.common.validationErrors"}</h2>
	<ul>
		{foreach from=$validationErrors item=validationError}
			<li>{$validationError->message|escape}</li>
		{/foreach}
	</ul>
{elseif $submissionsErrors}
	<h2>{translate key="plugins.importexport.common.errorsOccured"}</h2>
	{foreach from=$submissionsErrors item=submissionsErrorMessages name=submissionsErrors}
		{if $submissionsErrorMessages|@count > 0}
			<p>{$smarty.foreach.submissionsErrors.iteration}. {translate key="submission.submission"}</p>
			<ul>
				{foreach from=$submissionsErrorMessages item=submissionsErrorMessage}
					<li>{$submissionsErrorMessage|escape}</li>
				{/foreach}
			</ul>
		{/if}
	{/foreach}
{else}
	{translate key="plugins.importexport.native.importComplete"}
	<ul>
		{foreach from=$submissions item=submission}
			<li>{$submission->getLocalizedTitle()|strip_unsafe_html}</li>
		{/foreach}
	</ul>
{/if}
