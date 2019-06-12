{**
 * templates/frontend/pages/viewFile.tpl
 *
 * Copyright (c) 2014-2017 Simon Fraser University Library
 * Copyright (c) 2003-2017 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @brief Lightweight page for viewing files (usually loading PDF inline)
 *
 * @uses $publishedSubmission Monograph Monograph this file is attached to
 * @uses $publicationFormat
 * @uses $submissionFile SubmissionFile
 * @uses $viewableFileContent string Template output for the rendered view
 * @uses $downloadUrl string (optional) A URL to download this file
 *}

{* Get URL to the related book *}
{url|assign:"parentUrl" page="catalog" op="book" path=$publishedSubmission->getBestId()}

<!DOCTYPE html>
<html lang="{$currentLocale|replace:"_":"-"}" xml:lang="{$currentLocale|replace:"_":"-"}">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset={$defaultCharset|escape}" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>{translate key="catalog.viewableFile.title" type=$publicationFormat->getLocalizedName()|escape title=$submissionFile->getLocalizedName()|escape}</title>

	{load_header context="frontend" headers=$headers}
	{load_stylesheet context="frontend" stylesheets=$stylesheets}
</head>
<body class="pkp_page_{$requestedPage|escape} pkp_op_{$requestedOp|escape}">

	{* Header wrapper *}
	<header class="header_viewable_file">

		<a href="{$parentUrl}" class="return">
			<span class="pkp_screen_reader">
				{translate key="catalog.viewableFile.return" monographTitle=$publishedSubmission->getLocalizedTitle()|escape}
			</span>
		</a>

		<span class="title">
			{$submissionFile->getLocalizedName()|escape}
		</span>

		{if $downloadUrl}
			<a href="{$downloadUrl}" class="download" download>
				<span class="label">
					{translate key="common.download"}
				</span>
			</a>
		{/if}

	</header>

	{$viewableFileContent}

	{load_script context="frontend" scripts=$scripts}
	{call_hook name="Templates::Common::Footer::PageFooter"}

</body>
</html>
