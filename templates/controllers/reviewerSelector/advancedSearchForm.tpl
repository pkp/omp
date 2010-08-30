<!-- templates/controllers/reviewerSelector/advancedSearchForm.tpl -->

{**
 * advancedSearchForm.tpl
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Display reviewer advanced search form
 *
 *}
<!--  Need a random ID to give to modal elements so that they are unique in the DOM -->
{assign var='uniqueId' value=""|uniqid}

<script type="text/javascript">
{literal}
	$(function() {
		$('.button').button();

		// Initialize range selectors
		$("#doneRange").slider({ // Initialize the slider control
			range: true,
			min: {/literal}{$reviewerValues.doneMin}{literal},
			max: {/literal}{$reviewerValues.doneMax}{literal},
			values: [{/literal}{$reviewerValues.doneMin}{literal}, {/literal}{$reviewerValues.doneMax}{literal}],
			slide: function(event, ui) {
				$("#doneAmountLabel").val(ui.values[0] + ' - ' + ui.values[1]);
			}
		});
		$("#doneAmountLabel").val($("#doneRange").slider("values", 0) + ' - ' + $("#doneRange").slider("values", 1));  // Initialize the label above the slider
		$("#avgRange").slider({ // Initialize the slider control
			range: true,
			min: {/literal}{$reviewerValues.avgMin}{literal},
			max: {/literal}{$reviewerValues.avgMax}{literal},
			values: [{/literal}{$reviewerValues.avgMin}{literal}, {/literal}{$reviewerValues.avgMax}{literal}],
			slide: function(event, ui) {
				$("#avgAmountLabel").val(ui.values[0] + ' - ' + ui.values[1]);
			}
		});
		$("#avgAmountLabel").val(+ $("#avgRange").slider("values", 0) + ' - ' + $("#avgRange").slider("values", 1));  // Initialize the label above the slider
		$("#lastRange").slider({ // Initialize the slider control
			range: true,
			min: {/literal}{$reviewerValues.lastMin}{literal},
			max: {/literal}{$reviewerValues.lastMax}{literal},
			values: [{/literal}{$reviewerValues.lastMin}{literal}, {/literal}{$reviewerValues.lastMax}{literal}],
			slide: function(event, ui) {
				$("#lastAmountLabel").val(ui.values[0] + ' - ' + ui.values[1]);
			}
		});
		$("#lastAmountLabel").val(+ $("#lastRange").slider("values", 0) + ' - ' + $("#lastRange").slider("values", 1));  // Initialize the label above the slider
		$("#activeRange").slider({ // Initialize the slider control
			range: true,
			min: {/literal}{$reviewerValues.activeMin}{literal},
			max: {/literal}{$reviewerValues.activeMax}{literal},
			values: [{/literal}{$reviewerValues.activeMin}{literal}, {/literal}{$reviewerValues.activeMax}{literal}],
			slide: function(event, ui) {
				$("#activeAmountLabel").val(ui.values[0] + ' - ' + ui.values[1]);
			}
		});
		$("#activeAmountLabel").val($("#activeRange").slider("values", 0) + ' - ' + $("#activeRange").slider("values", 1));  // Initialize the label above the slider

		// Initialize reviewer interests search field
		$("#interests").tagit({
			availableTags: [{/literal}{$existingInterests}{literal}]
		});


		// Handler filter form submission
		$('#reviewerFilterForm').ajaxForm({
		        url: '{/literal}{url router=$smarty.const.ROUTE_COMPONENT component="grid.users.reviewerSelect.ReviewerSelectGridHandler" op="updateReviewerSelect"}{literal}',
				dataType: 'json',
				data: {"doneMin": getMaxValue('doneRange'), "doneMax": getMaxValue('doneRange'), "avgMin": getMinValue('avgRange'), "avgMax": getMaxValue('avgRange'),
					"lastMin": getMinValue('lastRange'), "lastMax": getMaxValue('lastRange'), "activeMin": getMinValue('activeRange'), "activeMax": getMaxValue('activeRange')},
		        // Load the new grid below
		        success: function(returnString) {
	    			//$('#loading').hide();
		    		if (returnString.status == true) {
		    			$('#reviewerSelectGridContainer').html(returnString.content);
		    		}
		    		//$('#loadingText').text(returnString.content);  // Set to error or success message
		        },
		        beforeSubmit: function(arr, $form, options) {
			        	// Need to reset form values to prevent cached values from being submitted
						$.each(arr, function(index, value) {
							switch(value.name) {
								case 'doneMin':
									getMinValue('doneRange');
									break;
								case 'doneMax':
									getMaxValue('doneRange');
									break;
								case 'avgMin':
									getMinValue('avgRange');
									break;
								case 'avgMax':
									getMaxValue('avgRange');
									break;
								case 'lastMin':
									getMinValue('lastRange');
									break;
								case 'lastMax':
									getMaxValue('lastRange');
									break;
								case 'activeMin':
									getMinValue('activeRange');
									break;
								case 'activeMax':
									getMaxValue('activeRange');
									break;
							}
						});
 		        }
		});
	});

	// Get max value for a range slider
	function getMaxValue(id) {
		var values = $("#" + id).slider("option", "values");
		return values[1];
	}
	// Get min value for a range slider
	function getMinValue(id) {
		var values = $("#" + id).slider("option", "values");
		return values[0];
	}
{/literal}
</script>

<form name="reviewerFilterForm" id="reviewerFilterForm" action="{url router=$smarty.const.ROUTE_COMPONENT component="grid.users.reviewerSelect.ReviewerSelectGridHandler" op="updateReviewerSelect"}" method="post">
{fbvFormArea id="reviewerSearchForm"}
	{fbvFormSection float=$fbvStyles.float.LEFT}
		<p class="sliderLabel">
			<label for="doneAmountLabel">{translate key="manager.reviewerSearch.doneAmount"}:</label>
			<input type="text" id="doneAmountLabel" class="sliderValue" />
		</p>
		<div id="doneRange" class="rangeSlider"></div>
	{/fbvFormSection}
	{fbvFormSection float=$fbvStyles.float.RIGHT}
		<p class="sliderLabel">
			<label for="avgAmountLabel">{translate key="manager.reviewerSearch.avgAmount"}:</label>
			<input type="text" id="avgAmountLabel" class="sliderValue" />
		</p>
		<div id="avgRange" class="rangeSlider"></div>
	{/fbvFormSection}
	{fbvFormSection float=$fbvStyles.float.LEFT}
		<p class="sliderLabel">
			<label for="lastAmountLabel">{translate key="manager.reviewerSearch.lastAmount"}:</label>
			<input type="text" id="lastAmountLabel" class="sliderValue" />
		</p>
		<div id="lastRange" class="rangeSlider"></div>
	{/fbvFormSection}
	{fbvFormSection float=$fbvStyles.float.RIGHT}
		<p class="sliderLabel">
			<label for="activeAmountLabel">{translate key="manager.reviewerSearch.activeAmount"}:</label>
			<input type="text" id="activeAmountLabel" class="sliderValue" />
		</p>
		<div id="activeRange" class="rangeSlider"></div>
	{/fbvFormSection}
	{fbvFormSection title="manager.reviewerSearch.interests" float=$fbvStyles.float.LEFT}
		<ul id="interests" style="padding-left: 10px;"></ul>
	{/fbvFormSection}
	{fbvFormSection}
		<input type="submit" class="button" id="submitFilter" value={translate key="common.refresh"} style="width: 60%; margin-left: 20%; margin-right: 20%;" />
	{/fbvFormSection}
{/fbvFormArea}
</form>
{url|assign:reviewerSelectGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.users.reviewerSelect.ReviewerSelectGridHandler" op="fetchGrid" monographId=$monographId doneMin=$reviewerValues.doneMin
	doneMax=$reviewerValues.doneMax avgMin=$reviewerValues.avgMin avgMax=$reviewerValues.avgMax lastMin=$reviewerValues.lastMin lastMax=$reviewerValues.lastMax activeMin=$reviewerValues.activeMin activeMax=$reviewerValues.activeMax escape=false}
{assign var=gridContainerId value="reviewerSelectGridContainer"|concat:"-":}
{load_url_in_div id=$gridContainerId url="$reviewerSelectGridUrl"}

<!-- / templates/controllers/reviewerSelector/advancedSearchForm.tpl -->

