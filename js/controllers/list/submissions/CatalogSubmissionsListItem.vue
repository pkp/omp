<template>
	<li class="pkpListPanelItem pkpListPanelItem--submission pkpListPanelItem--catalog" :class="{'--isLoading': isSaving, '--isFeatured': isFeatured}">
		<list-panel-item-orderer
			v-if="isOrdering"
			@itemOrderUp="itemOrderUp"
			@itemOrderDown="itemOrderDown"
			i18n="i18n"
			itemTitle="submission.title"
		/>
		<div class="pkpListPanelItem--submission__item">
			<a :href="submission.urlPublished">
				<div class="pkpListPanelItem--submission__title">
					{{ submission.title }}
				</div>
				<div v-if="submission.author" class="pkpListPanelItem--submission__author">
					{{ submission.author.authorString }}
				</div>
			</a>
			<div class="pkpListPanelItem__actions">
				<button @click.prevent="viewCatalogEntry">
					{{ i18n.editCatalogEntry }}
				</button>
				<a :href="submission.urlWorkflow">
					{{ i18n.viewSubmission }}
				</a>
			</div>
		</div>
		<div class="pkpListPanelItem__selectItem" @click.prevent="toggleFeatured">
			<label :for="featuredInputId">{{ i18n.featured }}</label>
			<input type="checkbox" :id="featuredInputId" :checked="isFeatured" @click.stop>
		</div>
		<div class="pkpListPanelItem__selectItem" @click.prevent="toggleNewRelease">
			<label :for="newReleaseInputId">{{ i18n.newRelease }}</label>
			<input type="checkbox" :id="newReleaseInputId" :checked="isNewRelease" @click.stop>
		</div>
		<div class="pkpListPanelItem__mask" :class="{'--active': isSaving}">
			<div class="pkpListPanelItem__maskLabel">
				<span class="pkpListPanelItem__maskLabel_loading">
					<span class="pkp_spinner"></span>
					{{ i18n.saving }}
				</span>
			</div>
		</div>
	</li>
</template>

<script>
import ListPanelItem from '../../../../lib/pkp/js/controllers/list/ListPanelItem.vue';
import ListPanelItemOrderer from '../../../../lib/pkp/js/controllers/list/ListPanelItemOrderer.vue';

export default _.extend({}, ListPanelItem, {
	name: 'CatalogSubmissionsListItem',
	props: ['submission', 'i18n', 'filterAssocType', 'filterAssocId', 'catalogEntryUrl', 'isOrdering', 'apiPath'],
	components: {
		ListPanelItemOrderer,
	},
	data: function() {
		return {
			isSaving: false,
		}
	},
	computed: {
		/**
		 * Map the submission id to the list item id
		 */
		id: function() {
			return this.submission.id;
		},

		/**
		 * Is the submission featured in the current filtered view?
		 * press, category or series
		 *
		 * @return bool
		 */
		isFeatured: function() {
			return typeof _.findWhere(this.submission.featured, {assoc_type: this.filterAssocType}) !== 'undefined';
		},

		/**
		 * Is the submission a new release in the current filtered view?
		 * press, category or series
		 *
		 * @return bool
		 */
		isNewRelease: function() {
			return typeof _.findWhere(this.submission.newRelease, {assoc_type: this.filterAssocType}) !== 'undefined';
		},

		/**
		 * The id attribute of the featured checkbox
		 *
		 * @return string
		 */
		featuredInputId: function() {
			return 'featured-' + this.submission.id.toString();
		},

		/**
		 * The id attribute of the new release checkbox
		 *
		 * @return string
		 */
		newReleaseInputId: function() {
			return 'newRelease-' + this.submission.id.toString();
		},
	},
	methods: _.extend({}, ListPanelItem.methods, {
		/**
		 * Toggle the checkbox when clicked
		 */
		toggleFeatured: function() {
			if (_.findWhere(this.submission.featured, {assoc_type: this.filterAssocType})) {
				this.submission.featured = _.reject(this.submission.featured, {assoc_type: this.filterAssocType});
			} else {
				this.submission.featured.push({
					assoc_type: this.filterAssocType,
					assoc_id: this.filterAssocId,
					seq: 1,
				});
			}
			this.saveDisplayFlags();
		},

		/**
		 * Toggle the checkbox when clicked
		 */
		toggleNewRelease: function() {
			if (_.findWhere(this.submission.newRelease, {assoc_type: this.filterAssocType})) {
				this.submission.newRelease = _.reject(this.submission.newRelease, {assoc_type: this.filterAssocType});
			} else {
				this.submission.newRelease.push({
					assoc_type: this.filterAssocType,
					assoc_id: this.filterAssocId,
					seq: 1,
				});
			}
			this.saveDisplayFlags();
		},

		/**
		 * Post updates to the featured or new release status of a submission
		 */
		saveDisplayFlags: function() {

			this.isLoading = true;

			var self = this;
			$.ajax({
				url: $.pkp.app.apiBaseUrl + '/' + this.apiPath + '/' + 'saveDisplayFlags',
				type: 'POST',
				data: {
					submissionId: this.submission.id,
					featured: this.submission.featured,
					newRelease: this.submission.newRelease,
					csrfToken: $.pkp.currentUser.csrfToken,
				},
				error: function(r) {
					self.ajaxErrorCallback(r);
				},
				success: function(r) {
					if (typeof r.featured !== 'undefined') {
						self.submission.featured = r.featured;
						self.$emit('catalogFeatureUpdated', self.submission);
					}
					if (typeof r.newRelease !== 'undefined') {
						self.submission.newRelease = r.newRelease;
					}
				},
				complete: function(r) {
					self.isLoading = false;
				}
			});
		},

		/**
		 * Launch a modal to view the catalog entry for this item
		 */
		viewCatalogEntry: function() {

			var opts = {
				title: this.i18n.catalogEntry,
				url: this.catalogEntryUrl.replace('__id__', this.submission.id),
			};

			$('<div id="' + $.pkp.classes.Helper.uuid() + '" ' +
					'class="pkp_modal pkpModalWrapper" tabindex="-1"></div>')
				.pkpHandler('$.pkp.controllers.modal.AjaxModalHandler', opts);
		},
	}),
});
</script>
