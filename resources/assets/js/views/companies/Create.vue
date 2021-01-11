<template>
  <base-page v-if="isSuperAdmin" class="item-create">
    <sw-page-header class="mb-3" :title="pageTitle">
      <sw-breadcrumb slot="breadcrumbs">
        <sw-breadcrumb-item to="/admin/dashboard" :title="$t('general.home')" />
        <sw-breadcrumb-item to="/admin/companies" :title="$tc('companies.company', 2)" />
        <sw-breadcrumb-item
          v-if="$route.name === 'companies.edit'"
          to="#"
          :title="$t('companies.edit_company')"
          active
        />
        <sw-breadcrumb-item
          v-else
          to="#"
          :title="$t('companies.add_company')"
          active
        />
      </sw-breadcrumb>
      <template slot="actions"></template>
    </sw-page-header>

    <div class="grid grid-cols-12">
      <div class="col-span-12 md:col-span-8">
        <form action="" @submit.prevent="submitCompany">
          <sw-card>
            <sw-input-group
              :label="$t('companies.name')"
              :error="nameError"
              class="mb-4"
              required
            >
              <sw-input
                v-model.trim="formData.name"
                :invalid="$v.formData.name.$error"
                class="mt-2"
                focus
                type="text"
                name="name"
                @input="$v.formData.name.$touch()"
              />
            </sw-input-group>

            <sw-input-group
              :label="$t('companies.nif')"
              :error="nifError"
              class="mb-4"
            >
              <sw-input
                v-model.trim="formData.nif"
                :invalid="$v.formData.nif.$error"
                class="mt-2"
                focus
                type="text"
                name="nif"
                @input="$v.formData.nif.$touch()"
              />
            </sw-input-group>

            <sw-input-group
              :label="$tc('settings.company_info.country')"
              :error="countryError"
              required
            >
              <sw-select
                v-model="country"
                :options="countries"
                :class="{ error: $v.formData.country_id.$error }"
                :searchable="true"
                :show-labels="false"
                :allow-empty="false"
                :placeholder="$t('general.select_country')"
                class="mt-2"
                label="name"
                track-by="id"
              />
            </sw-input-group>

            <div class="mt-6 mb-4">
              <sw-button
                :loading="isLoading"
                variant="primary"
                type="submit"
                size="lg"
                class="flex justify-center w-full md:w-auto"
              >
                <save-icon v-if="!isLoading" class="mr-2 -ml-1" />
                {{ isEdit ? $t('companies.update_company') : $t('companies.save_company') }}
              </sw-button>
            </div>
          </sw-card>
        </form>
      </div>
    </div>
  </base-page>
</template>

<script>
import { mapActions, mapGetters } from 'vuex'
const {
  required,
  minLength,
  email,
  numeric,
  minValue,
  maxLength,
  requiredIf,
} = require('vuelidate/lib/validators')

export default {
  data() {
    return {
      isLoading: false,
      title: 'Add Company',

      formData: {
        name: '',
        nif: '',
        country_id: null,
      },
      country: null,
    }
  },
  watch: {
    country(newCountry) {
      this.formData.country_id = newCountry.id
      if (this.isFetchingData) {
        return true
      }
    },
  },
  computed: {
    ...mapGetters('user', ['currentUser']),

    ...mapGetters(['countries']),

    ...mapGetters('companies', ['companies']),

    isSuperAdmin() {
      return this.currentUser.role == 'super admin'
    },

    pageTitle() {
      if (this.$route.name === 'companies.edit') {
        return this.$t('companies.edit_company')
      }
      return this.$t('companies.add_company')
    },

    isEdit() {
      if (this.$route.name === 'companies.edit') {
        return true
      }
      return false
    },

    nameError() {
      if (!this.$v.formData.name.$error) {
        return ''
      }
      if (!this.$v.formData.name.required) {
        return this.$tc('validation.required')
      }
    },

    nifError() {
      if (!this.$v.formData.nif.$error) {
        return ''
      }
      if (!this.$v.formData.nif.maxLength) {
        return this.$tc('companies.nif_maxlength')
      }
    },

    countryError() {
      if (!this.$v.formData.country_id.$error) {
        return ''
      }
      if (!this.$v.formData.country_id.required) {
        return this.$tc('validation.required')
      }
    },

  },

  created() {
    if (!this.isSuperAdmin) {
      this.$router.push('/admin/dashboard')
    }
    if (this.isEdit) {
      this.loadEditData()
    }
    else{
      this.fetchCompanies();
    }

  },

  mounted() {
    this.$v.formData.$reset()
  },
  validations: {
    formData: {
      name: {
        required,
      },
      nif: {
        maxLength: maxLength(9),
      },
      country_id: {
        required,
      },   
    },
  },

  methods: {

    ...mapActions('companies', [
      'fetchCompanies',
      'addCompany',
      'fetchCompany'
    ]),

    async loadEditData() {

      let response = await this.fetchCompany(this.$route.params.id)

      if (response.data) {
        this.formData = { ...this.formData, ...response.data.company }
      }

      if (this.formData.company_id) {
        
        await this.fetchCompanies();
        
        this.company = this.companies.find(
          (_company) => this.formData.company_id === _company.id
        )

      }
      else{
        this.fetchCompanies();
      }
      
    },

    async submitCompany() {
      this.$v.formData.$touch()

      if (this.$v.$invalid) {
        return true
      }

      try {
        let response
        this.isLoading = true
        if (this.isEdit) {
          response = await this.updateCompany(this.formData)
          let data
          if (response.data.success) {
            window.toastr['success'](this.$tc('companies.updated_message'))
            this.$router.push('/admin/companies')
            this.isLoading = false
          }
        } else {
          response = await this.addCompany(this.formData)
          let data
          if (response.data.success) {
            this.isLoading = false
            if (!this.isEdit) {
              window.toastr['success'](this.$tc('companies.created_message'))
              this.$router.push('/admin/companies')
              return true
            }
          }
        }
      } catch (err) {
        this.isLoading = false
      }
    },
  },
}
</script>
