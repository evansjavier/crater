<template>
  <base-page>
    <sw-page-header :title="$t('items.title')">
      <sw-breadcrumb slot="breadcrumbs">
        <sw-breadcrumb-item :title="$t('general.home')" to="dashboard" />
        <sw-breadcrumb-item :title="$tc('items.item', 2)" to="#" active />
      </sw-breadcrumb>

      <template slot="actions">
        <sw-button
          v-show="totalItems"
          variant="primary-outline"
          size="lg"
          @click="toggleFilter"
        >
          {{ $t('general.filter') }}
          <component :is="filterIcon" class="w-4 h-4 ml-2 -mr-1" />
        </sw-button>

        <sw-button
          tag-name="router-link"
          to="items/create"
          variant="primary"
          size="lg"
          class="ml-4"
        >
          <plus-icon class="w-6 h-6 mr-1 -ml-2" />
          {{ $t('items.add_item') }}
        </sw-button>
      </template>
    </sw-page-header>

    <slide-y-up-transition>
      <sw-filter-wrapper v-show="showFilters">
        <sw-input-group :label="$tc('items.name')" class="flex-1 mt-2 ml-0">
          <sw-input
            v-model="filters.name"
            type="text"
            name="name"
            class="mt-2"
            autocomplete="off"
          />
        </sw-input-group>

        <sw-input-group
          :label="$tc('items.unit')"
          class="flex-1 mt-2 ml-0 lg:ml-6"
        >
          <sw-select
            v-model="filters.unit"
            :options="itemUnits"
            :searchable="true"
            :show-labels="false"
            :placeholder="$t('items.select_a_unit')"
            class="mt-2"
            label="name"
            autocomplete="off"
          />
        </sw-input-group>

        <sw-input-group
          :label="$tc('items.price')"
          class="flex-1 mt-2 ml-0 lg:ml-6"
        >
          <sw-input
            v-model="filters.price"
            type="text"
            name="name"
            class="mt-2"
            autocomplete="off"
          />
        </sw-input-group>

        <label
          class="absolute text-sm leading-snug text-gray-900 cursor-pointer"
          style="top: 10px; right: 15px"
          @click="clearFilter"
        >
          {{ $t('general.clear_all') }}</label
        >
      </sw-filter-wrapper>
    </slide-y-up-transition>

    <sw-empty-table-placeholder
      v-show="showEmptyScreen"
      :title="$t('items.no_items')"
      :description="$t('items.list_of_items')"
    >
      <satellite-icon class="mt-5 mb-4" />

      <sw-button
        slot="actions"
        tag-name="router-link"
        to="/admin/items/create"
        size="lg"
        variant="primary-outline"
      >
        <plus-icon class="w-6 h-6 mr-1 -ml-2" />
        {{ $t('items.add_new_item') }}
      </sw-button>
    </sw-empty-table-placeholder>

    <div v-show="!showEmptyScreen" class="grid grid-cols-1 md:grid-cols-2">

      <div class="relative table-container">
        <div
          class="relative flex items-center justify-between h-10 mt-5 list-none border-b-2 border-gray-200 border-solid"
        >
          <p class="text-sm">
            {{ $t('general.showing') }}: <b>{{ items.length }}</b>
            {{ $t('general.of') }} <b>{{ totalItems }}</b>
          </p>

          <sw-transition>
            <sw-dropdown v-if="selectedItems.length">
              <span
                slot="activator"
                class="flex block text-sm font-medium cursor-pointer select-none text-primary-400"
              >
                {{ $t('general.actions') }}
                <chevron-down-icon class="h-5" />
              </span>

              <sw-dropdown-item @click="removeMultipleItems">
                <trash-icon class="h-5 mr-3 text-gray-600" />
                {{ $t('general.delete') }}
              </sw-dropdown-item>
            </sw-dropdown>
          </sw-transition>
        </div>

        <div class="absolute z-10 items-center pl-4 mt-2 select-none md:mt-12">
          <sw-checkbox
            v-model="selectAllFieldStatus"
            variant="primary"
            size="sm"
            class="hidden md:inline"
            @change="selectAllItems"
          />

          <sw-checkbox
            v-model="selectAllFieldStatus"
            :label="$t('general.select_all')"
            variant="primary"
            size="sm"
            class="md:hidden"
            @change="selectAllItems"
          />
        </div>

        <sw-table-component
          ref="table"
          :data="fetchData"
          :show-filter="false"
          table-class="table"
        >
          <sw-table-column
            :sortable="false"
            :filterable="false"
            cell-class="no-click"
          >
            <div slot-scope="row" class="custom-control custom-checkbox">
              <sw-checkbox
                :id="row.id"
                v-model="selectField"
                :value="row.id"
                variant="primary"
                size="sm"
              />
            </div>
          </sw-table-column>

          <sw-table-column :sortable="true" :label="$t('items.name')" show="name">
            <template slot-scope="row">
              <span>{{ $t('items.name') }}</span>
              <router-link
                :to="{ path: `items/${row.id}/edit` }"
                class="font-medium text-primary-500"
              >
                {{ row.name }}
              </router-link>
            </template>
          </sw-table-column>

          <sw-table-column
            :sortable="true"
            :label="$t('items.updated_at')"
            sort-as="updated_at"
            show="formattedUpdatedAt"
          />

          <sw-table-column
            :sortable="true"
            :label="$t('items.price')"
            show="price"
          >
            <template slot-scope="row">
              <span> {{ $t('items.price') }} </span>

              <div v-html="$utils.formatMoney(row.price, defaultCurrency)" />
            </template>
          </sw-table-column>

          <sw-table-column
            :sortable="true"
            :label="$t('items.stock')"
            sort-as="stock"
            show="stock"
          />

          <sw-table-column
            :sortable="true"
            :label="$t('items.unit')"
            show="unit_name"
          >
            <template slot-scope="row">
              <span>{{ $t('items.unit') }}</span>

              <span>
                {{ row.unit_name ? row.unit_name : 'Not selected' }}
              </span>
            </template>
          </sw-table-column>

          <sw-table-column
            :sortable="true"
            :filterable="false"
            cell-class="action-dropdown"
          >
            <template slot-scope="row">
              <span> {{ $t('items.action') }} </span>

              <sw-dropdown>
                <dot-icon slot="activator" />

                <sw-dropdown-item @click="showMovements(row.id)">
                  <eye-icon class="h-5 mr-3 text-gray-600" />
                    {{ $t('items.show_movements') }}
                </sw-dropdown-item>

                <sw-dropdown-item @click="addMovement(row.id)">
                  <plus-icon class="h-5 mr-3 text-gray-600" />
                    {{ $t('items.add_new_movement') }}
                </sw-dropdown-item>

                <sw-dropdown-item
                  :to="`items/${row.id}/edit`"
                  tag-name="router-link"
                >
                  <pencil-icon class="h-5 mr-3 text-gray-600" />
                  {{ $t('general.edit') }}
                </sw-dropdown-item>

                <sw-dropdown-item @click="removeItems(row.id)">
                  <trash-icon class="h-5 mr-3 text-gray-600" />
                  {{ $t('general.delete') }}
                </sw-dropdown-item>
              </sw-dropdown>
            </template>
          </sw-table-column>
        </sw-table-component>

      </div>
      <div class="mt-7 md:pl-8" v-if="showItemMovementsId">
        <h3>{{ $t('items.movements_title') }}</h3>

        <sw-table-component
          ref="table_movements"
          :data="movements"
          :show-filter="false"
          table-class="table"
        >
          <sw-table-column
            :sortable="false"
            :label="$t('items.stock')"            
            show="formattedMovementDate"
          />

          <sw-table-column
            :sortable="false"
            :label="$t('movements.quantity')"
            show="quantity"
          />

          <sw-table-column
            :sortable="false"
            :label="$t('movements.type')"
            show="type"
          >
            <template slot-scope="row">
              <span>{{ $t('movements.type') }}</span>
              <span>
                {{ $t('movements.' + row.type) }}
              </span>
            </template>
          </sw-table-column>

          <sw-table-column
            :sortable="false"
            :filterable="false"
            cell-class="action-dropdown"
          >
            <template slot-scope="row">
              <span> {{ $t('items.action') }} </span>

              <sw-dropdown>
                <dot-icon slot="activator" />

                <sw-dropdown-item
                  :to="`movement/${row.id}/edit`"
                  tag-name="router-link"
                >
                  <pencil-icon class="h-5 mr-3 text-gray-600" />
                  {{ $t('general.edit') }}
                </sw-dropdown-item>

                <sw-dropdown-item @click="removeMovement(row.id)">
                  <trash-icon class="h-5 mr-3 text-gray-600" />
                  {{ $t('general.delete') }}
                </sw-dropdown-item>
              </sw-dropdown>
            </template>
          </sw-table-column>

        </sw-table-component>

      </div>

    </div>
  </base-page>
</template>

<script>
import { mapActions, mapGetters } from 'vuex'
import {
  FilterIcon,
  XIcon,
  ChevronDownIcon,
  PencilIcon,
  TrashIcon,
  PlusIcon,
  EyeIcon,
} from '@vue-hero-icons/solid'
import SatelliteIcon from '../../components/icon/SatelliteIcon'

export default {
  components: {
    SatelliteIcon,
    FilterIcon,
    XIcon,
    PlusIcon,
    ChevronDownIcon,
    PencilIcon,
    TrashIcon,
    EyeIcon,
  },

  data() {
    return {
      id: null,
      showFilters: false,
      sortedBy: 'updated_at',
      isRequestOngoing: true,
      showItemMovementsId : null,

      movements : [],

      filters: {
        name: '',
        unit: '',
        price: '',
      },
    }
  },

  computed: {
    ...mapGetters('item', [
      'items',
      'selectedItems',
      'totalItems',
      'selectAllField',
      'itemUnits',
    ]),

    ...mapGetters('company', ['defaultCurrency']),

    showEmptyScreen() {
      return !this.totalItems && !this.isRequestOngoing
    },

    filterIcon() {
      return this.showFilters ? 'x-icon' : 'filter-icon'
    },

    selectField: {
      get: function () {
        return this.selectedItems
      },
      set: function (val) {
        this.selectItem(val)
      },
    },

    selectAllFieldStatus: {
      get: function () {
        return this.selectAllField
      },
      set: function (val) {
        this.setSelectAllState(val)
      },
    },
  },

  watch: {
    filters: {
      handler: 'setFilters',
      deep: true,
    },
  },

  mounted() {
    this.fetchItemUnits({ limit: 'all' })
    this.showItemMovementsId =  parseInt(this.$route.query.show);

    this.fetchMovements();

    if(this.showItemMovementsId){
      this.selectItem([this.showItemMovementsId])
    }

  },

  destroyed() {
    if (this.selectAllField) {
      this.selectAllItems()
    }
  },

  methods: {
    ...mapActions('item', [
      'fetchItems',
      'selectAllItems',
      'selectItem',
      'deleteItem',
      'deleteMultipleItems',
      'setSelectAllState',
      'fetchItemUnits',
      'fetchItemMovements',
      'deleteMovement',
    ]),

    refreshTable() {
      this.$refs.table.refresh()
    },

    showMovements(item_id){
      this.showItemMovementsId = item_id;
      this.fetchMovements();

      this.selectItem([item_id])

      this.$router.push({ path: '/admin/items', query: { show: item_id } })      
    },

    refreshMovements(){

      this.showItemMovementsId =  parseInt(this.$route.query.show);

      this.fetchMovements();

      this.selectItem([showItemMovementsId])

    },

    addMovement(item_id){
      this.$router.push({ name: 'movements.create', params: { id: item_id } })
    },

    async fetchMovements(){
      let movements = await this.fetchItemMovements(this.showItemMovementsId);
      if(movements.data){
        this.movements = movements.data;
      }
    },

    async fetchData({ page, filter, sort }) {
      let data = {
        search: this.filters.name !== null ? this.filters.name : '',
        unit_id: this.filters.unit !== null ? this.filters.unit.id : '',
        price: Math.round(this.filters.price * 100),
        orderByField: sort.fieldName || 'updated_at',
        orderBy: sort.order || 'desc',
        page,
      }

      this.isRequestOngoing = true
      let response = await this.fetchItems(data)
      this.isRequestOngoing = false

      return {
        data: response.data.items.data,
        pagination: {
          totalPages: response.data.items.last_page,
          currentPage: page,
        },
      }
    },

    setFilters() {
      this.refreshTable()
    },

    clearFilter() {
      this.filters = {
        name: '',
        unit: '',
        price: '',
      }
    },

    toggleFilter() {
      if (this.showFilters) {
        this.clearFilter()
      }

      this.showFilters = !this.showFilters
    },

    async removeItems(id) {
      this.id = id
      swal({
        title: this.$t('general.are_you_sure'),
        text: this.$tc('items.confirm_delete'),
        icon: '/assets/icon/trash-solid.svg',
        buttons: true,
        dangerMode: true,
      }).then(async (willDelete) => {
        if (willDelete) {
          let res = await this.deleteItem({ ids: [id] })

          if (res.data.success) {
            window.toastr['success'](this.$tc('items.deleted_message', 1))
            this.$refs.table.refresh()
            return true
          }

          if (res.data.error === 'item_attached') {
            window.toastr['error'](
              this.$tc('items.item_attached_message'),
              this.$t('general.action_failed')
            )
            return true
          }

          window.toastr['error'](res.data.message)
          return true
        }
      })
    },

    async removeMultipleItems() {
      swal({
        title: this.$t('general.are_you_sure'),
        text: this.$tc('items.confirm_delete', 2),
        icon: '/assets/icon/trash-solid.svg',
        buttons: true,
        dangerMode: true,
      }).then(async (willDelete) => {
        if (willDelete) {
          let res = await this.deleteMultipleItems()

          if (res.data.success || res.data.items) {
            window.toastr['success'](this.$tc('items.deleted_message', 2))
            this.$refs.table.refresh()
          } else if (res.data.error) {
            window.toastr['error'](res.data.message)
          }
        }
      })
    },

    async removeMovement(id) {
      this.id = id
      swal({
        title: this.$t('general.are_you_sure'),
        text: this.$tc('movements.confirm_delete'),
        icon: '/assets/icon/trash-solid.svg',
        buttons: true,
        dangerMode: true,
      }).then(async (willDelete) => {
        if (willDelete) {
          let res = await this.deleteMovement(id)

          if (res.data.success) {
            window.toastr['success'](this.$tc('movements.deleted_message', 1))
            this.refreshTable();
            this.refreshMovements();
            return true
          }

          window.toastr['error'](res.data.message)
          return true
        }
      })
    },
  },
}
</script>
