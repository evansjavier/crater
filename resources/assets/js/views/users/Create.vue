<template>
  <base-page v-if="isSuperAdmin" class="item-create">
    <sw-page-header class="mb-3" :title="pageTitle">
      <sw-breadcrumb slot="breadcrumbs">
        <sw-breadcrumb-item to="/admin/dashboard" :title="$t('general.home')" />
        <sw-breadcrumb-item to="/admin/users" :title="$tc('users.user', 2)" />
        <sw-breadcrumb-item
          v-if="$route.name === 'users.edit'"
          to="#"
          :title="$t('users.edit_user')"
          active
        />
        <sw-breadcrumb-item
          v-else
          to="#"
          :title="$t('users.new_user')"
          active
        />
      </sw-breadcrumb>
      <template slot="actions"></template>
    </sw-page-header>

    <div class="grid grid-cols-12">
      <div class="col-span-12 md:col-span-8">
        <form action="" @submit.prevent="submitUser">
          <sw-card>
            <sw-input-group
              :label="$t('users.name')"
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
              :label="$t('users.email')"
              class="mt-4"
              :error="emailError"
              required
            >
              <sw-input
                :invalid="$v.formData.email.$error"
                v-model.trim="formData.email"
                type="text"
                name="email"
                tab-index="3"
                @input="$v.formData.email.$touch()"
              />
            </sw-input-group>

            <sw-input-group
              :label="$tc('users.password')"
              :error="passwordError"
              required
              class="mt-4"
            >
              <sw-input
                v-model="formData.password"
                :invalid="$v.formData.password.$error"
                type="password"
                class="mt-2"
                @input="$v.formData.password.$touch()"
              />
            </sw-input-group>

            <sw-input-group :label="$t('users.phone')" class="mt-4 mb-6">
              <sw-input
                v-model.trim="formData.phone"
                type="text"
                name="phone"
                tab-index="4"
              />
            </sw-input-group>

            <sw-input-group
              :label="$t('users.company')"
              :error="companyError"
              class="mb-4"
              required
            >
              <sw-select
                v-model="company"
                :options="getCompanies"
                :searchable="true"
                :show-labels="false"
                :allow-empty="true"
                :multiple="false"
                class="mt-2"
                track-by="id"
                label="name"
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
                {{ isEdit ? $t('users.update_user') : $t('users.save_user') }}
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
      title: 'Add User',

      formData: {
        name: '',
        email: null,
        password: null,
        phone: null,
        company_id : null,        
      },

      company: null,
    }
  },
  watch: {
    company(newCompany) {
      this.formData.company_id = newCompany.id
      // if (this.isFetchingData) {
      //   return true
      // }
    },
  },
  computed: {
    ...mapGetters('user', ['currentUser']),

    ...mapGetters('companies', ['companies']),

    isSuperAdmin() {
      return this.currentUser.role == 'super admin'
    },

    pageTitle() {
      if (this.$route.name === 'users.edit') {
        return this.$t('users.edit_user')
      }
      return this.$t('users.new_user')
    },

    isEdit() {
      if (this.$route.name === 'users.edit') {
        return true
      }
      return false
    },

    getCompanies() {
      return this.companies;
    },

    nameError() {
      if (!this.$v.formData.name.$error) {
        return ''
      }
      if (!this.$v.formData.name.required) {
        return this.$t('validation.required')
      }
      if (!this.$v.formData.name.minLength) {
        return this.$tc(
          'validation.name_min_length',
          this.$v.formData.name.$params.minLength.min,
          { count: this.$v.formData.name.$params.minLength.min }
        )
      }
    },

    emailError() {
      if (!this.$v.formData.email.$error) {
        return ''
      }

      if (!this.$v.formData.email.email) {
        return this.$tc('validation.email_incorrect')
      }

      if (!this.$v.formData.email.required) {
        return this.$tc('validation.required')
      }
    },

    passwordError() {
      if (!this.$v.formData.password.$error) {
        return ''
      }
      if (!this.$v.formData.password.required) {
        return this.$t('validation.required')
      }
      if (!this.$v.formData.password.minLength) {
        return this.$tc(
          'validation.password_min_length',
          this.$v.formData.password.$params.minLength.min,
          { count: this.$v.formData.password.$params.minLength.min }
        )
      }
    },

      companyError() {
      if (!this.$v.formData.company_id.$error) {
        return ''
      }

      if (!this.$v.formData.company_id.required) {
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
      this.fetchCompanies({limit : 'all'});
    }

  },

  mounted() {
    this.$v.formData.$reset()
  },
  validations: {
    formData: {
      name: {
        required,
        minLength: minLength(3),
      },
      email: {
        email,
        required,
      },

      password: {
        required: requiredIf(function () {
          return !this.isEdit
        }),
        minLength: minLength(8),
      },
      company_id: {
        required
      },
    },
  },

  methods: {
    ...mapActions('users', ['addUser', 'fetchUser', 'updateUser']),

    ...mapActions('companies', [
      'fetchCompanies'      
    ]),

    async loadEditData() {

      let response = await this.fetchUser(this.$route.params.id)

      if (response.data) {
        this.formData = { ...this.formData, ...response.data.user }
      }

      if (this.formData.company_id) {
        
        await this.fetchCompanies({limit : 'all'});
        
        this.company = this.companies.find(
          (_company) => this.formData.company_id === _company.id
        )

      }
      
    },

    async submitUser() {
      this.$v.formData.$touch()

      if (this.$v.$invalid) {
        return true
      }

      try {
        let response
        this.isLoading = true
        if (this.isEdit) {
          response = await this.updateUser(this.formData)
          let data
          if (response.data.success) {
            window.toastr['success'](this.$tc('users.updated_message'))
            this.$router.push('/admin/users')
            this.isLoading = false
          }
          if (response.data.error) {
            window.toastr['error'](this.$t('validation.email_already_taken'))
          }
        } else {
          response = await this.addUser(this.formData)
          let data
          if (response.data.success) {
            this.isLoading = false
            if (!this.isEdit) {
              window.toastr['success'](this.$tc('users.created_message'))
              this.$router.push('/admin/users')
              return true
            }
          }
        }
      } catch (err) {
        if (err.response.data.errors.email) {
          this.isLoading = false
        }
      }
    },
  },
}
</script>
