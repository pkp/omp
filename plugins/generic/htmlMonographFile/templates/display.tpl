{**
 * plugins/generic/htmlMonographFile/templates/display.tpl
 *
 * Copyright (c) 2014-2020 Simon Fraser University
 * Copyright (c) 2003-2020 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * Embedded viewing of a HTML galley.
 *}
<!DOCTYPE html>
<html lang="{$currentLocale|replace:"_":"-"}" xml:lang="{$currentLocale|replace:"_":"-"}">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset={$defaultCharset|escape}" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>{translate key="catalog.viewableFile.title" type=$publicationFormat->getLocalizedName()|escape title=$submissionFile->getLocalizedName()|escape}</title>

	{load_header context="frontend" headers=$headers}
	{load_stylesheet context="frontend" stylesheets=$stylesheets}
	{load_script context="frontend" scripts=$scripts}
</head>
<body class="pkp_page_{$requestedPage|escape} pkp_op_{$requestedOp|escape}">

	{* Header wrapper *}
	<header class="header_viewable_file">

		{capture assign="submissionUrl"}{url op="book" path=$publishedSubmission->getBestId()}{/capture}

		<a href="{$submissionUrl}" class="return">
			<span class="pkp_screen_reader">
				{translate key="monograph.return"}
			</span>
		</a>

		<a href="{url page="catalog" op="book" path=$monograph->getBestId()|to_array:$publicationFormat->getBestId():$downloadFile->getBestId()}" class="title">
			{$monograph->getLocalizedTitle()|escape}
		</a>
	</header>

	<div id="htmlContainer" class="viewable_file_frame{if !$isLatestPublication} viewable_file_frame_with_notice{/if}" style="overflow:visible;-webkit-overflow-scrolling:touch">
		{if !$isLatestPublication}
			<div class="viewable_file_frame_notice">
				<div class="viewable_file_frame_notice_message" role="alert">
					{translate key="submission.outdatedVersion" datePublished=$filePublication->getData('datePublished') urlRecentVersion=$submissionUrl}
				</div>
			</div>
		{/if}
		<iframe name="htmlFrame" src="{$downloadUrl}" title="{translate key="submission.representationOfTitle" representation=$publicationFormat->getLocalizedName() title=$filePublication->getLocalizedFullTitle()|escape}" allowfullscreen webkitallowfullscreen></iframe>
	</div>
	{call_hook name="Templates::Common::Footer::PageFooter"}
</body>
</html>
