<template>
	<div class="pkpListPanel pkpListPanel--submissions pkpListPanel--catalogSubmissions" :class="classLoading">
		<div class="pkpListPanel__header">
			<div class="pkpListPanel__title">{{ i18n.title }}</div>
			<ul class="pkpListPanel__actions">
				<li v-if="hasFilters">
					<button @click.prevent="toggleFilter" :class="{'--isActive': this.isFilterVisible}">
						<span class="fa fa-filter"></span>
						{{ i18n.filter }}
					</button>
				</li>
				<li>
					<a href="#" @click.prevent="openNewEntryModal">{{ i18n.add }}</a>
				</li>
			</ul>
			<list-panel-search
				@searchPhraseChanged="set"
				:isSearching="isSearching"
				:searchPhrase="searchPhrase"
				:i18n="i18n"
			/>
		</div>
		<div class="pkpListPanel__body pkpListPanel__body--catalogSubmissions">
			<catalog-submissions-list-filter
				v-if="hasFilters"
				@filterList="updateFilter"
				:isVisible="isFilterVisible"
				:categories="categories"
				:series="series"
				:i18n="i18n"
			/>
			<div class="pkpListPanel__content pkpListPanel__content--catalogSubmissions">
				<div class="pkpListPanel__columnLabels pkpListPanel__columnLabels--catalogSubmissions">
					<span class="pkpListPanel__columnLabel">
						<span>{{ featuredLabel }}</span>
					</span>
					<span class="pkpListPanel__columnLabel">
						<span>{{ newReleaseLabel }}</span>
					</span>
				</div>
				<ul class="pkpListPanel__items">
					<catalog-submissions-list-item
						v-for="item in collection.items"
						@catalogFeatureUpdated="sortByFeaturedSequence"
						:submission="item"
						:catalogEntryUrl="catalogEntryUrl"
						:filterAssocType="filterAssocType"
						:filterAssocId="filterAssocId"
						:apiPath="apiPath"
						:i18n="i18n"
					/>
				</ul>
			</div>
		</div>
		<div class="pkpListPanel__footer">
			<list-panel-load-more
				v-if="canLoadMore"
				@loadMore="loadMore"
				:isLoading="isLoading"
				:i18n="i18n"
			/>
			<list-panel-count
				:count="itemCount"
				:total="this.collection.maxItems"
				:i18n="i18n"
			/>
		</div>
	</div>
</template>

<script>
import SubmissionsListPanel from '../../../../lib/pkp/js/controllers/list/submissions/SubmissionsListPanel.vue';
import CatalogSubmissionsListItem from './CatalogSubmissionsListItem.vue';
import CatalogSubmissionsListFilter from './CatalogSubmissionsListFilter.vue';

export default _.extend({}, SubmissionsListPanel, {
	name: 'CatalogSubmissionsListPanel',
	components: _.extend({}, SubmissionsListPanel.components, {
		CatalogSubmissionsListItem,
		CatalogSubmissionsListFilter,
	}),
	data: function() {
		return _.extend({}, SubmissionsListPanel.data(), {
			constants: {},
		});
	},
	computed: _.extend({}, SubmissionsListPanel.computed, {
		/**
		 * Are there any filters available?
		 */
		hasFilters: function() {
			return (this.categories.length + this.series.length) > 0;
		},

		/**
		 * Return the appropriate label for the featured column depending on
		 * if we're looking at a filtered view
		 */
		featuredLabel: function() {
			if (this.filterAssocType === this.constants.assocTypes.category) {
				return this.i18n.featuredCategory;
			} else if (this.filterAssocType === this.constants.assocTypes.series) {
				return this.i18n.featuredSeries;
			}
			return this.i18n.featured;
		},

		/**
		 * Return the appropriate label for the new release column depending on
		 * if we're looking at a filtered view
		 */
		newReleaseLabel: function() {
			if (this.filterAssocType === this.constants.assocTypes.category) {
				return this.i18n.newReleaseCategory;
			} else if (this.filterAssocType === this.constants.assocTypes.series) {
				return this.i18n.newReleaseSeries;
			}
			return this.i18n.newRelease;
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
				return this.constants.assocTypes.category;
			} else if (_.has(this.filterParams, 'seriesIds')) {
				return this.constants.assocTypes.series;
			}
			return this.constants.assocTypes.press;
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
	}),
	methods: _.extend({}, SubmissionsListPanel.methods, {
		/**
		 * Open the new catalog entry modal
		 */
		openNewEntryModal: function() {

			var opts = {
				title: this.i18n.add,
				url: this.addUrl,
			};

			$('<div id="' + $.pkp.classes.Helper.uuid() + '" ' +
					'class="pkp_modal pkpModalWrapper" tabindex="-1"></div>')
				.pkpHandler('$.pkp.controllers.modal.AjaxModalHandler', opts);
		},

		/**
		 * Sort submissions by featured sequence
		 */
		sortByFeaturedSequence: function() {
			this.collection.items = _.sortBy(this.collection.items, function(submission) {
				var featured = _.findWhere(submission.featured, {assoc_type: this.filterAssocType});
				return typeof featured === 'undefined' ? 9999999 : featured.seq;
			}, this);
		},

		/**
		 * Set the sort order for get requests. This can change depending on
		 * whether the full catalog or a series/category is being viewed.
		 */
		updateSortOrder: function() {
			if (typeof this.filterParams.categoryIds !== 'undefined') {
				var cat = _.findWhere(this.categories, {id: this.filterParams.categoryIds});
				this.getParams.orderBy = cat.sortBy;
				this.getParams.orderDirection = cat.sortDir || this.constants.catalogSortDir;
			} else if (typeof this.filterParams.seriesIds !== 'undefined') {
				var series = _.findWhere(this.series, {id: this.filterParams.seriesIds});
				this.getParams.orderBy = series.sortBy || this.constants.catalogSortBy;
				this.getParams.orderDirection = series.sortDir || this.constants.catalogSortDir;
				console.log('sort', series, series.sortBy, series.sortDir, this.getParams);
			} else {
				this.getParams.orderBy = this.constants.catalogSortBy;
				this.getParams.orderDirection = this.constants.catalogSortDir;
			}
		},
	}),
	mounted: function() {

		/**
		 * When a filter is set, update the sort order to match the setting of
		 * the series or catalog
		 *
		 * Set this watcher before calling SubmissionsListPanel.mounted() so
		 * that the get params are updated before the ajax request is made.
		 */
		this.$watch('filterParams', function(newVal, oldVal) {
			if (newVal === oldVal) {
				return;
			}
			this.updateSortOrder();
		});

		SubmissionsListPanel.mounted.call(this);

		/**
		 * Resort featured items to the top of the collection whenever it
		 * changes
		 */
		this.sortByFeaturedSequence();
		this.$watch('collection', function(newVal, oldVal) {
			if (oldVal === newVal) {
				return;
			}
			this.sortByFeaturedSequence();
		});

		/**
		 * Update when a new entry has been added to the catalog
		 */
		var self = this;
		pkp.eventBus.$on('catalogEntryAdded', function(data) {
			self.get();
		});
	}
});
</script>
