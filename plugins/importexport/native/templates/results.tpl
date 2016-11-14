{**
 * plugins/importexport/native/templates/results.tpl
 *
 * Copyright (c) 2014-2016 Simon Fraser University Library
 * Copyright (c) 2003-2016 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * List of operations this plugin can perform
 *}

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
