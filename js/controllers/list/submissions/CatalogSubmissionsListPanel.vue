<template>
	<div class="pkpListPanel pkpListPanel--submissions pkpListPanel--catalogSubmissions" :class="classStatus">
		<div class="pkpListPanel__header">
			<div class="pkpListPanel__title">{{ i18n.title }}</div>
			<ul class="pkpListPanel__actions">
				<li>
					<button @click.prevent="toggleFilter" :class="{'--isActive': isFilterVisible}">
						<span class="fa fa-filter"></span>
						{{ i18n.filter }}
					</button>
				</li>
				<li class="pkpListPanel__orderToggle" v-if="canOrder">
					<button @click.prevent="toggleOrdering" :class="{'--isActive': isOrdering}">
						<span class="fa fa-sort"></span>
						<template v-if="isOrdering">
							{{ i18n.saveFeatureOrder }}
						</template>
						<template v-else>
							{{ i18n.orderFeatures }}
						</template>
					</button>
				</li>
				<li v-if="isOrdering" class="pkpListPanel__orderToggleCancel">
					<button @click.prevent="cancelOrdering" class="--isWarnable">
						{{ i18n.cancel }}
					</button>
				</li>
				<li>
					<button @click.prevent="openNewEntryModal">
						{{ i18n.add }}
					</button>
				</li>
			</ul>
			<list-panel-search
				@searchPhraseChanged="setSearchPhrase"
				:isSearching="isSearching"
				:searchPhrase="searchPhrase"
				:i18n="i18n"
			/>
		</div>
		<div class="pkpListPanel__body pkpListPanel__body--catalogSubmissions">
			<div v-if="isOrdering" class="pkpListPanel__notice pkpListPanel__notice--info" tabindex="0">
				<span class="fa fa-info-circle"></span>
				{{ featuredNotice }}
			</div>
			<catalog-submissions-list-filter
				@filterList="updateFilter"
				:isVisible="isFilterVisible"
				:filters="filters"
				:i18n="i18n"
			/>
			<div class="pkpListPanel__content pkpListPanel__content--catalogSubmissions">
				<div v-if="collection.items.length" class="pkpListPanel__columnLabels pkpListPanel__columnLabels--catalogSubmissions">
					<span class="pkpListPanel__columnLabel">
						<span>{{ featuredLabel }}</span>
					</span>
					<span class="pkpListPanel__columnLabel">
						<span>{{ newReleaseLabel }}</span>
					</span>
				</div>
				<ul class="pkpListPanel__items" aria-live="polite">
					<draggable v-model="collection.items" :options="draggableOptions" @start="drag=true" @end="drag=false">
						<catalog-submissions-list-item
							v-for="item in collection.items"
							@catalogFeatureUpdated="sortByFeaturedSequence"
							@itemOrderUp="itemOrderUp"
							@itemOrderDown="itemOrderDown"
							:submission="item"
							:catalogEntryUrl="catalogEntryUrl"
							:filterAssocType="filterAssocType"
							:filterAssocId="filterAssocId"
							:isOrdering="isOrdering"
							:apiPath="apiPath"
							:i18n="i18n"
						/>
					</draggable>
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
import draggable from 'vuedraggable';

export default {
	extends: SubmissionsListPanel,
	name: 'CatalogSubmissionsListPanel',
	components: {
		CatalogSubmissionsListItem,
		CatalogSubmissionsListFilter,
		draggable,
	},
	data: function() {
		return {
			constants: {},
		};
	},
	computed: {
		/**
		 * Can any monographs be ordered?
		 */
		canOrder: function() {
			var canOrder = false;
			_.each(this.collection.items, function(item) {
				if (typeof _.findWhere(item.featured, {assoc_type: this.filterAssocType}) !== 'undefined') {
					canOrder = true;
					return false;
				}
			}, this);
			return canOrder;
		},

		/**
		 * Set status on the component
		 */
		classStatus: function() {
			return { '--isLoading': this.isLoading, '--isOrdering': this.isOrdering };
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
		 * Return the appropriate label for the featured column depending on
		 * if we're looking at a filtered view
		 */
		featuredNotice: function() {
			if (this.filterAssocType === this.constants.assocTypes.category) {
				return this.__('orderingFeaturesSection', {title: _.findWhere(this.filters.categoryIds.filters, {val: this.filterAssocId}).title});
			} else if (this.filterAssocType === this.constants.assocTypes.series) {
				return this.__('orderingFeaturesSection', {title: _.findWhere(this.filters.seriesIds.filters, {val: this.filterAssocId}).title});
			}
			return this.i18n.orderingFeatures;
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
				return this.filterParams.categoryIds[0];
			} else if (_.has(this.filterParams, 'seriesIds')) {
				return this.filterParams.seriesIds[0];
			}
			// in OMP, there's only one press context and it's always 1
			return 1;
		},
	},
	methods: {
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
				var cat = _.findWhere(this.filters.categoryIds.filters, {val: this.filterParams.categoryIds[0]});
				this.getParams.orderBy = cat.sortBy;
				this.getParams.orderDirection = cat.sortDir || this.constants.catalogSortDir;
			} else if (typeof this.filterParams.seriesIds !== 'undefined') {
				var series = _.findWhere(this.filters.seriesIds.filters, {val: this.filterParams.seriesIds[0]});
				this.getParams.orderBy = series.sortBy || this.constants.catalogSortBy;
				this.getParams.orderDirection = series.sortDir || this.constants.catalogSortDir;
			} else {
				this.getParams.orderBy = this.constants.catalogSortBy;
				this.getParams.orderDirection = this.constants.catalogSortDir;
			}
		},

		/**
		 * Update the order sequence property for items in this list based on
		 * the new order of items
		 */
		setItemOrderSequence: function() {
			var featured = [],
				seq = 0;
			_.each(this.collection.items, function(item) {
				var feature = _.findWhere(item.featured, {assoc_type: this.filterAssocType});
				if (typeof feature !== 'undefined') {
					feature.seq = seq;
					featured.push({
						id: item.id,
						seq: feature.seq,
					});
					seq++;
				}
			}, this);

			this.isLoading = true;

			var self = this;
			$.ajax({
				url: this.getApiUrl(this.apiPath + '/' + 'saveFeaturedOrder'),
				type: 'POST',
				data: {
					assocType: this.filterAssocType,
					assocId: this.filterAssocId,
					featured: featured,
					csrfToken: $.pkp.currentUser.csrfToken,
				},
				error: function(r) {
					self.ajaxErrorCallback(r);
				},
				complete: function(r) {
					self.isLoading = false;
				}
			});
		},

		/**
		 * Override the ListPanel method to only handle featured items
		 */
		itemOrderDown: function(data) {
			var featuredItems = _.filter(this.collection.items, function(item) {
				return typeof _.findWhere(item.featured, {assoc_type: this.filterAssocType}) !== 'undefined';
			}, this);
			var index = _.findIndex(this.collection.items, function(item) { return item.id == data.id });
			if (index === featuredItems.length - 1) {
				return;
			}
			this.collection.items.splice(index + 1, 0, this.collection.items.splice(index, 1)[0]);
			this.itemOrderResetFocus(data.id, 'down');
		},
	},
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
};
</script>
