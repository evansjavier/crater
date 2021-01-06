<template>
  <base-page v-if="isSuperAdmin" class="items">
    <sw-page-header :title="$t('companies.title')">
      <sw-breadcrumb slot="breadcrumbs">
        <sw-breadcrumb-item to="dashboard" :title="$t('general.home')" />
        <sw-breadcrumb-item to="#" :title="$tc('companies.title', 2)" active />
      </sw-breadcrumb>

      <template slot="actions">
        <sw-button
          v-show="totalCompanies"
          variant="primary-outline"
          size="lg"
          @click="toggleFilter"
        >
          {{ $t('general.filter') }}
          <component :is="filterIcon" class="w-4 h-4 ml-2 -mr-1" />
        </sw-button>

        <sw-button
          tag-name="router-link"
          to="companies/create"
          variant="primary"
          size="lg"
          class="ml-4"
        >
          <plus-icon class="w-6 h-6 mr-1 -ml-2" />
          {{ $t('companies.add_company') }}
        </sw-button>
      </template>
    </sw-page-header>

    <slide-y-up-transition>
      <sw-filter-wrapper v-show="showFilters" class="mt-3">
        <sw-input-group :label="$tc('companies.name')" class="flex-1 mt-2 mr-4">
          <sw-input
            v-model="filters.name"
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
      :title="$t('users.no_users')"
      :description="$t('users.list_of_users')"
    >
      <astronaut-icon class="mt-5 mb-4" />

      <sw-button
        slot="actions"
        tag-name="router-link"
        to="/admin/companies/create"
        size="lg"
        variant="primary-outline"
      >
        <plus-icon class="w-6 h-6 mr-1 -ml-2" />
        {{ $t('companies.add_company') }}
      </sw-button>
    </sw-empty-table-placeholder>

    <div class="relative table-container" v-show="!showEmptyScreen">
      <div
        class="relative flex items-center justify-between h-10 mt-5 list-none border-b-2 border-gray-200 border-solid"
      >
        <p class="text-sm">
          {{ $t('general.showing') }}: <b>{{ companies.length }}</b>

          {{ $t('general.of') }}

          <b>{{ totalCompanies }}</b>
        </p>

      </div>

      <sw-table-component
        ref="table"
        :data="fetchData"
        :show-filter="false"
        table-class="table"
      >

        <sw-table-column :sortable="true" :label="$t('users.name')" show="name">
          <template slot-scope="row">
            <span>{{ $t('users.name') }}</span>
            <span class="font-medium text-primary-500">{{ row.name }}</span>
          </template>
        </sw-table-column>

        <sw-table-column
          :sortable="true"
          :label="$t('companies.added_on')"
          sort-as="created_at"
          show="adminFormattedCreatedAt"
        />
      </sw-table-component>
    </div>
  </base-page>
</template>

<script>
import { mapActions, mapGetters } from 'vuex'
import AstronautIcon from '@/components/icon/AstronautIcon'
import {
  FilterIcon,
  XIcon,
  ChevronDownIcon,
  PencilIcon,
  TrashIcon,
  PlusIcon,
} from '@vue-hero-icons/solid'

export default {
  components: {
    AstronautIcon,
    FilterIcon,
    XIcon,
    ChevronDownIcon,
    PencilIcon,
    TrashIcon,
    PlusIcon,
  },

  data() {
    return {
      id: null,
      showFilters: false,
      sortedBy: 'created_at',
      isRequestOngoing: true,

      filters: {
        name: '',
      },
    }
  },
  computed: {
    ...mapGetters('user', ['currentUser']),
    ...mapGetters('users', [
      'users',
      // 'selectedUsers',
      // 'selectAllField',
    ]),

    ...mapGetters('companies', [
      'companies',
      'selectedCompanies',
      'totalCompanies',
      'selectAllField',
    ]),    
    isSuperAdmin() {
      return this.currentUser.role == 'super admin'
    },
    showEmptyScreen() {
      return !this.totalCompanies && !this.isRequestOngoing
    },

    filterIcon() {
      return this.showFilters ? 'x-icon' : 'filter-icon'
    },

    selectField: {
      get: function () {
        return this.selectedCompanies
      },
      set: function (val) {
        this.selectedCompany(val)
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
  created() {
    if (!this.isSuperAdmin) {
      this.$router.push('/admin/dashboard')
    }
  },
  watch: {
    filters: {
      handler: 'setFilters',
      deep: true,
    },
  },

  destroyed() {
    if (this.selectAllField) {
      this.selectAllCompanies()
    }
  },

  methods: {
    ...mapActions('companies', [
      'fetchCompanies',
      'selectAllCompanies',
      'selectedCompany',
      'deleteCompany',
      'deleteMultipleCompanies',
      'setSelectAllState'
    ]),

    refreshTable() {
      this.$refs.table.refresh()
    },

    async fetchData({ page, filter, sort }) {
      let data = {
        display_name: this.filters.name !== null ? this.filters.name : '',

        orderByField: sort.fieldName || 'created_at',
        orderBy: sort.order || 'desc',
        page,
      }

      this.isRequestOngoing = true

      let response = await this.fetchCompanies(data)

      this.isRequestOngoing = false

      return {
        data: response.data.companies.data,
        pagination: {
          totalPages: response.data.companies.last_page,
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
      }
    },

    toggleFilter() {
      if (this.showFilters) {
        this.clearFilter()
      }

      this.showFilters = !this.showFilters
    },

    async removeCompany(id) {
      let company = []
      company.push(id)

      swal({
        title: this.$t('general.are_you_sure'),
        text: this.$tc('companies.confirm_delete'),
        icon: '/assets/icon/trash-solid.svg',
        buttons: true,
        dangerMode: true,
      }).then(async (willDelete) => {
        if (willDelete) {
          let res = await this.deleteCompany(company)

          if (res.data.success) {
            window.toastr['success'](this.$tc('companies.deleted_message', 1))
            this.$refs.table.refresh()
            return true
          }

          if (res.data.error === 'company_attached') {
            window.toastr['error'](
              this.$tc('companies.company_attached_message'),
              this.$t('general.action_failed')
            )
            return true
          }

          window.toastr['error'](res.data.message)
          return true
        }
      })
    },

    async removeMultipleCompanies() {
      swal({
        title: this.$t('general.are_you_sure'),
        text: this.$tc('companies.confirm_delete', 2),
        icon: '/assets/icon/trash-solid.svg',
        buttons: true,
        dangerMode: true,
      }).then(async (willDelete) => {
        if (willDelete) {
          let res = await this.deleteMultipleCompanies()

          if (res.data.success || res.data.companies) {
            window.toastr['success'](this.$tc('companies.deleted_message', 2))
            this.$refs.table.refresh()
          } else if (res.data.error) {
            window.toastr['error'](res.data.message)
          }
        }
      })
    },
  },
}
</script>
