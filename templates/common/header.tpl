{**
 * header.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Common site header.
 *
 *}
{strip}
{translate|assign:"applicationName" key="common.openMonographPress"}
{if !$pageTitleTranslated}{translate|assign:"pageTitleTranslated" key=$pageTitle}{/if}
{if $pageCrumbTitle}
	{translate|assign:"pageCrumbTitleTranslated" key=$pageCrumbTitle}
{elseif !$pageCrumbTitleTranslated}
	{assign var="pageCrumbTitleTranslated" value=$pageTitleTranslated}
{/if}
{/strip}<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset={$defaultCharset|escape}" />
	<title>{$pageTitleTranslated}</title>
	<meta name="description" content="{$metaSearchDescription|escape}" />
	<meta name="keywords" content="{$metaSearchKeywords|escape}" />
	<meta name="generator" content="{$applicationName} {$currentVersionString|escape}" />
	{$metaCustomHeaders}
	{if $displayFavicon}<link rel="icon" href="{$faviconDir}/{$displayFavicon.uploadName|escape:"url"}" />{/if}

	<link rel="stylesheet" type="text/css" media="all" href="{$baseUrl}/styles/omp.css" />

	{call_hook|assign:"leftSidebarCode" name="Templates::Common::LeftSidebar"}
	{call_hook|assign:"rightSidebarCode" name="Templates::Common::RightSidebar"}

	{foreach from=$stylesheets item=cssUrl}
		<link rel="stylesheet" href="{$cssUrl}" type="text/css" />
	{/foreach}

	<!-- Base Jquery -->
	{if $allowCDN}<script src="http://www.google.com/jsapi"></script>
	<script>
		google.load("jquery", "1");
		google.load("jqueryui", "1");
	</script>
	{else}
	<script type="text/javascript" src="{$baseUrl}/lib/pkp/js/lib/jquery/jquery.min.js"></script>
	<script type="text/javascript" src="{$baseUrl}/lib/pkp/js/lib/jquery/plugins/jqueryUi.min.js"></script>
	{/if}

	<!-- UI elements (menus, forms, etc) -->
	<script type="text/javascript" src="{$baseUrl}/lib/pkp/js/lib/superfish/hoverIntent.js"></script>
	<script type="text/javascript" src="{$baseUrl}/lib/pkp/js/lib/superfish/superfish.js"></script>
	<script type="text/javascript" src="{$baseUrl}/lib/pkp/js/lib/wufoo/wufoo.js"></script>
	<script type="text/javascript" src="{$baseUrl}/lib/pkp/js/jquery.pnotify.js"></script>

	<!-- Modals/Confirms -->
	<script type="text/javascript" src="{$baseUrl}/lib/pkp/js/lib/jquery/plugins/validate/jquery.validate.min.js"></script>
	<script type="text/javascript" src="{$baseUrl}/lib/pkp/js/jqueryValidatorI18n.js"></script>
	<script type="text/javascript" src="{$baseUrl}/lib/pkp/js/modal.js"></script>

	<!-- ListBuilder -->
	<script type="text/javascript" src="{$baseUrl}/lib/pkp/js/listbuilder.js"></script>

	<!-- Other Jquery Plugins -->
	<script type="text/javascript" src="{$baseUrl}/lib/pkp/js/lib/jquery/plugins/jquery.form.js"></script>
	<script type="text/javascript" src="{$baseUrl}/lib/pkp/js/ajax_upload.js"></script>
	<script type="text/javascript" src="{$baseUrl}/lib/pkp/js/jquery.cookie.js"></script>
	<script type="text/javascript" src="{$baseUrl}/lib/pkp/js/fontController.js"></script>
	<script type="text/javascript" src="{$baseUrl}/lib/pkp/js/tag-it.js"></script>

	<!-- General JS -->
	<script type="text/javascript" src="{$baseUrl}/lib/pkp/js/general.js"></script>

	<script type="text/javascript">
        // initialise plugins
		{literal}
        $(function(){
            $('ul.sf-menu').superfish(); // Initialize the navigation menu
			jqueryValidatorI18n("{/literal}{$baseUrl}{literal}", "{/literal}{$currentLocale}{literal}"); // include the appropriate validation localization
			fontSize("#sizer", ".page", 9, 12, 20); // Initialize the font sizer
			$('.button').button();
			$('a.settings').live("click", (function() { // Initialize grid settings button handler
				$(this).parent().siblings('.row_controls').toggle(300);
			}));
			{/literal}{if $validateId}{literal}
			$("form[name={/literal}{$validateId}{literal}]").validate({
				errorClass: "error",
				highlight: function(element, errorClass) {
					$(element).parent().parent().addClass(errorClass);
				},
				unhighlight: function(element, errorClass) {
					$(element).parent().parent().removeClass(errorClass);
				}
			});
			{/literal}{/if}{literal}
		});
		{/literal}
    </script>

	{$additionalHeadData}
</head>
<body>
<div class="page {$cssBodyClass} {$liquid}">

<div class="head">

{include file="common/sitenav.tpl"}

<div class="masthead">
<h1>
{if $displayPageHeaderLogo && is_array($displayPageHeaderLogo)}
	<img src="{$publicFilesDir}/{$displayPageHeaderLogo.uploadName|escape:"url"}" width="{$displayPageHeaderLogo.width|escape}" height="{$displayPageHeaderLogo.height|escape}" {if $displayPageHeaderLogoAltText != ''}alt="{$displayPageHeaderLogoAltText|escape}"{else}alt="{translate key="common.pageHeaderLogo.altText"}"{/if} />
{/if}
{if $displayPageHeaderTitle && is_array($displayPageHeaderTitle)}
	<img src="{$publicFilesDir}/{$displayPageHeaderTitle.uploadName|escape:"url"}" width="{$displayPageHeaderTitle.width|escape}" height="{$displayPageHeaderTitle.height|escape}" {if $displayPageHeaderTitleAltText != ''}alt="{$displayPageHeaderTitleAltText|escape}"{else}alt="{translate key="common.pageHeader.altText"}"{/if} />
{elseif $displayPageHeaderTitle}
	{$displayPageHeaderTitle}
{elseif $alternatePageHeader}
	{$alternatePageHeader}
{elseif $customLogoTemplate}
	{include file=$customLogoTemplate}
{elseif $siteTitle}
	{$siteTitle}
{else}
	{$applicationName}
{/if}
</h1>
</div> <!-- /masthead -->

{include file="common/localnav.tpl"}

{include file="common/breadcrumbs.tpl"}

</div> <!-- /head -->

<div class="body">

{if $isUserLoggedIn}
<div id="toolbox" class="rightCol toolbox mod simple">
    <div class="mod simple">
        <b class="top"><b class="tl"></b><b class="tr"></b></b>
        <div class="inner">
            <div class="hd">
                <h3>Toolbox</h3>
            </div>
            {$rightSidebarCode}
        </div>
        <b class="bottom"><b class="bl"></b><b class="br"></b></b>
    </div>
</div>
{/if}

<div class="main">

<table class='data' style='margin-left: 10px;'>
	<tr>
	<td><h2>{$pageTitleTranslated}</h2></td>
	{if $additionalHeading}<td style="width:40%;"></td><td>{$additionalHeading}</td>{/if}
	</tr>
</table>

{if $pageSubtitle && !$pageSubtitleTranslated}{translate|assign:"pageSubtitleTranslated" key=$pageSubtitle}{/if}
{if $pageSubtitleTranslated}
	<h3>{$pageSubtitleTranslated}</h3>
{/if}

<div id="content">
