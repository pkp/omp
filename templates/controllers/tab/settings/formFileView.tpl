{**
 * controllers/tab/settings/formImageView.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Form file view.
 *
 *}

{translate key="common.fileName"}: <a href="{$publicFilesDir}/{$file.uploadName|escape:"url"}" class="file">{$file.name|escape}</a>
{$file.dateUploaded|date_format:$datetimeFormatShort}
<div id="{$deleteLinkAction->getId()}" class="pkp_linkActions">
	{include file="linkAction/linkAction.tpl" action=$deleteLinkAction contextId=$fileSettingName}
</div>