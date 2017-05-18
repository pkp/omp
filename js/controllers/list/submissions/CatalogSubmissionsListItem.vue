<template>
	<li class="pkpListPanelItem pkpListPanelItem--submission pkpListPanelItem--catalog" :class="{'--isLoading': isSaving}">
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
export default {
	name: 'CatalogSubmissionsListItem',
	props: ['submission', 'i18n', 'filterParams', 'assocTypes', 'catalogEntryUrl', 'apiPath'],
	data: function() {
		return {
			isSaving: false,
		}
	},
	computed: {
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

		/**
		 * The assoc_type value which matches the current filter
		 *
		 * The assoc_type will match constants indicating a press, category or
		 * series
		 *
		 * @return int
		 */
		filterAssocType: function() {
			if (_.has(this.filterParams, 'categoryIds')) {
				return this.assocTypes.category;
			} else if (_.has(this.filterParams, 'seriesIds')) {
				return this.assocTypes.series;
			}
			return this.assocTypes.press;
		},

		/**
		 * The assoc_id value which matches the current filter
		 *
		 * The assoc_id will match the pressId, categoryId or seriesId
		 *
		 * @return int
		 */
		filterAssocId: function() {
			if (_.has(this.filterParams, 'categoryIds')) {
				return this.filterParams.categoryIds;
			} else if (_.has(this.filterParams, 'seriesIds')) {
				return this.filterParams.seriesIds;
			}
			// in OMP, there's only one press context and it's always 1
			return 1;
		},
	},
	methods: {
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

			this.isSaving = true;

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
					}
					if (typeof r.newRelease !== 'undefined') {
						self.submission.newRelease = r.newRelease;
					}
				},
				complete: function(r) {
					self.isSaving = false;
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
		}
	}
};
</script>
