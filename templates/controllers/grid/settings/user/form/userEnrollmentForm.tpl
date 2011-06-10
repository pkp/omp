{**
 * controllers/grid/settings/user/form/userEnrollmentForm.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Form for managing user enrollment.
 *}
<script type="text/javascript">
	<!--
	$(document).ready(function() {ldelim}
		// User select drop-down list
		$('#selectUser').change(function() {ldelim}
			var newUserId = $('#selectUser').val();
			// If we're already editing a user, submit the form
			if ($('#userId').length) {ldelim}
				var oldUserId = $('#userId').val();
				$('#userForm').ajaxSubmit({ldelim}
					dataType: 'json',
					success: function(jsonData) {ldelim}
						// Form errors, display errors and reset select drop-down
						if (jsonData !== null && jsonData.status === false) {ldelim}
							$('#selectUser').val(oldUserId);
							$('#userFormContainer').replaceWith(jsonData.content);
						// No form errors, display new edit form for selected user
						{rdelim} else if (jsonData !== null && jsonData.status === true) {ldelim}
							if (newUserId) {ldelim}
								$.post(
									'{url op="editUser"}',
									{ldelim}
										'userId': newUserId
									{rdelim},
									function(jsonData) {ldelim}
										if (jsonData !== null && jsonData.status === true) {ldelim}
											$('#userFormContainer').replaceWith(jsonData.content);
										{rdelim}
									{rdelim},
									'json'
								);
							{rdelim} else {ldelim}
								// No user selected, remove edit form
								$('#userFormContainer').empty();
							{rdelim}
						{rdelim}
					{rdelim}
				{rdelim});
			{rdelim} else {ldelim}
				// Not already editing a user, just display edit form for selected user
				if (newUserId) {ldelim}
					$.post(
						'{url op="editUser"}',
						{ldelim}
							'userId': newUserId
						{rdelim},
						function(jsonData) {ldelim}
							if (jsonData !== null && jsonData.status === true) {ldelim}
								$('#userFormContainer').replaceWith(jsonData.content);
							{rdelim}
						{rdelim},
						'json'
					);
				{rdelim}
			{rdelim}
		{rdelim});

	{rdelim});
	// -->
</script>

<div id="selectUserContainer" class="full left">

<h3>{translate key="grid.user.selectUser"}</h3>

<form class="pkp_form" id="userSelectForm" method="post" action="{url op="enrollUserFinish"}">

{fbvFormArea id="userSelectFormArea"}

{fbvFormSection}
	{fbvElement type="select" name="selectUser" id="selectUser" from=$userOptions translate="0"}
{/fbvFormSection}

{/fbvFormArea}

</form>
</div>

<div id="userFormContainer">
</div>
