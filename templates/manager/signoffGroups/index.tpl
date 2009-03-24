{**
 * index.tpl
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Display review signoffs.
 *
 * $Id$
 *
 *}
{strip}
{translate|assign:"pageTitleTranslated" key="manager.people.roleEnrollment" role=$roleName|translate}
{include file="common/header.tpl"}
{/strip}

<a href="{url op="setup" path=6}"><< Back to Step 6</a>

<h3>Groups</h3>
<a href="{url op="viewSignoffEntities" path=$reviewTypeId entity=$smarty.const.SIGNOFF_ENTITY_TYPE_GROUP}">Add Group</a>
<br />
<br />
{foreach from=$signoffEntities[$smarty.const.SIGNOFF_ENTITY_TYPE_GROUP] item=group}

{$group->getGroupTitle()} <a href="{url op="removeSignoffGroup" path=$reviewTypeId groupId=$group->getGroupId()}">remove</a>

{/foreach}

<h3>Users</h3>
<a href="{url op="viewSignoffEntities" path=$reviewTypeId entity=$smarty.const.SIGNOFF_ENTITY_TYPE_USER}">Add User</a>
<br />
<br />
{foreach from=$signoffEntities[$smarty.const.SIGNOFF_ENTITY_TYPE_USER] item=user}

{$user->getFullName()}<br />
{/foreach}


{include file="common/footer.tpl"}