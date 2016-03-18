{**
 * templates/frontend/pages/viewFile.tpl
 *
 * Copyright (c) 2014-2016 Simon Fraser University Library
 * Copyright (c) 2003-2016 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @brief Lightweight page for viewing files (usually loading PDF inline)
 *
 * @uses $publishedMonograph Monograph Monograph this file is attached to
 * @uses $publicationFormat
 * @uses $submissionFile SubmissionFile
 * @uses $viewableFileContent string Template output for the rendered view
 * @uses $downloadUrl string (optional) A URL to download this file
 *}

{* Get URL to the related book *}
{url|assign:"parentUrl" page="catalog" op="book" path=$publishedMonograph->getId()}

<!DOCTYPE html>
<html lang="{$currentLocale|replace:"_":"-"}" xml:lang="{$currentLocale|replace:"_":"-"}">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset={$defaultCharset|escape}" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>{translate key="catalog.viewableFile.title" type=$publicationFormat->getLocalizedName()|escape title=$submissionFile->getLocalizedName()|escape}</title>
	<meta name="generator" content="{$applicationName} {$currentVersionString|escape}" />
	<!-- Base Jquery -->

	{if $allowCDN}
		<script src="//ajax.googleapis.com/ajax/libs/jquery/{$smarty.const.CDN_JQUERY_VERSION}/{if $useMinifiedJavaScript}jquery.min.js{else}jquery.js{/if}"></script>
		<script src="//ajax.googleapis.com/ajax/libs/jqueryui/{$smarty.const.CDN_JQUERY_UI_VERSION}/{if $useMinifiedJavaScript}jquery-ui.min.js{else}jquery-ui.js{/if}"></script>
	{else}
		<script src="{$baseUrl}/lib/pkp/lib/vendor/components/jquery/{if $useMinifiedJavaScript}jquery.min.js{else}jquery.js{/if}"></script>
		<script src="{$baseUrl}/lib/pkp/lib/vendor/components/jqueryui/{if $useMinifiedJavaScript}jquery-ui.min.js{else}jquery-ui.js{/if}"></script>
	{/if}

	{* Load Noto Sans font from Google Font CDN *}
	{* To load extended latin or other character sets, see https://www.google.com/fonts#UsePlace:use/Collection:Noto+Sans *}
	<link href='//fonts.googleapis.com/css?family=Noto+Sans:400,400italic,700,700italic' rel='stylesheet' type='text/css'>

	{$metaCustomHeaders}

	{if $displayFavicon}<link rel="icon" href="{$faviconDir}/{$displayFavicon.uploadName|escape:"url"}" type="{$displayFavicon.mimeType|escape}" />{/if}

	{load_stylesheet context="frontend" stylesheets=$stylesheets}
</head>
<body class="pkp_page_{$requestedPage|escape} pkp_op_{$requestedOp|escape}">

	{* Header wrapper *}
	<header class="header_viewable_file">

		<a href="{$parentUrl}" class="return">
			<span class="pkp_screen_reader">
				{translate key="catalog.viewableFile.return" monographTitle=$publishedMonograph->getLocalizedTitle()|escape}
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

	{call_hook name="Templates::Common::Footer::PageFooter"}

</body>
</html>
