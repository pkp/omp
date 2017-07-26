<template>
	<div class="pkpListPanel__filter pkpListPanel__filter--catalogSubmissions" :class="{'--isVisible': isVisible}" :aria-hidden="!isVisible">
		<div class="pkpListPanel__filterHeader pkpListPanel__filterHeader--catalogSubmissions" tabindex="0">
			<span class="fa fa-filter"></span>
			{{ i18n.filter }}
		</div>
		<div class="pkpListPanel__filterOptions pkpListPanel__filterOptions--catalogSubmissions">
			<div v-if="categories.length > 0" class="pkpListPanel__filterSet">
				<div class="pkpListPanel__filterSetLabel">
					{{ i18n.categories }}
				</div>
				<ul>
					<li v-for="category in categories">
						<a href="#"
							@click.prevent.stop="filterByCategory(category.id)"
							class="pkpListPanel__filterLabel"
							:class="{'--isActive': isFilterActive('category', category.id)}"
							:tabindex="tabIndex"
						>{{ category.title }}</a>
						<button
							v-if="isFilterActive('category', category.id)"
							href="#"
							class="pkpListPanel__filterRemove"
							@click.prevent.stop="clearFilters()"
						>
							<span class="fa fa-times-circle-o"></span>
							<span class="pkpListPanel__filterRemoveLabel">{{ __('filterRemove', {filterTitle: category.title}) }}</span>
						</button>
					</li>
				</ul>
			</div>
			<div v-if="series.length > 0" class="pkpListPanel__filterSet">
				<div class="pkpListPanel__filterSetLabel">
					{{ i18n.series}}
				</div>
				<ul>
					<li v-for="seriesItem in series">
						<a href="#"
							@click.prevent.stop="filterBySeries(seriesItem.id)"
							class="pkpListPanel__filterLabel"
							:class="{'--isActive': isFilterActive('series', seriesItem.id)}"
							:tabindex="tabIndex"
						>{{ seriesItem.title }}</a>
						<button
							v-if="isFilterActive('series', seriesItem.id)"
							href="#"
							class="pkpListPanel__filterRemove"
							@click.prevent.stop="clearFilters()"
						>
							<span class="fa fa-times-circle-o"></span>
							<span class="pkpListPanel__filterRemoveLabel">{{ __('filterRemove', {filterTitle: seriesItem.title}) }}</span>
						</button>
					</li>
				</ul>
			</div>
		</div>
	</div>
</template>

<script>
import ListPanelFilter from '../../../../lib/pkp/js/controllers/list/ListPanelFilter.vue';

export default {
	extends: ListPanelFilter,
	name: 'CatalogSubmissionsListFilter',
	props: ['isVisible', 'categories', 'series', 'i18n'],
	methods: {
		/**
		 * Check if a filter is currently active
		 */
		isFilterActive: function(type, id) {
			return typeof _.findWhere(this.activeFilters, {type: type, id: id}) !== 'undefined';
		},

		/**
		 * Filter by a category
		 */
		filterByCategory: function(id) {
			if (this.isFilterActive('category', id)) {
				this.clearFilters();
				return;
			}
			this.clearFilters();
			this.activeFilters.push({type: 'category', id: id});
			this.filterList({categoryIds: id});
		},

		/**
		 * Filter by a series
		 */
		filterBySeries: function(id) {
			if (this.isFilterActive('series', id)) {
				this.clearFilters();
				return;
			}
			this.clearFilters();
			this.activeFilters.push({type: 'series', id: id});
			this.filterList({seriesIds: id});
		},
	},
};
</script>
