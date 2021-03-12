<template>
  <base-page>
    <!-- Page Header -->
    <sw-page-header :title="pageTitle" class="mb-3">
      <sw-breadcrumb slot="breadcrumbs">
        <sw-breadcrumb-item :title="$t('general.home')" to="/admin/dashboard" />
        <sw-breadcrumb-item :title="$tc('items.item', 2)" to="/admin/items" />
        <sw-breadcrumb-item
          v-if="$route.name === 'movements.edit'"
          :title="$t('movements.edit_movement')"
          to="#"
          active
        />
        <sw-breadcrumb-item
          v-else
          :title="$t('movements.new_movement')"
          to="#"
          active
        />
      </sw-breadcrumb>
    </sw-page-header>

    <div class="grid grid-cols-12">
      <div class="col-span-12 md:col-span-6">
        <form action="" @submit.prevent="submitItem">
          
          <sw-card>

            <sw-input-group
              :label="$t('movements.item')"
              class="mb-4"
              
            >
              {{ formData.item ? formData.item.name : "" }}
            </sw-input-group>


          <sw-input-group
            :label="$t('movements.quantity')"
            :error="quantityError"
            class="mb-4"
            required
          >
            <sw-input
              v-model="formData.quantity"
              :invalid="$v.formData.quantity.$error"
              @input="$v.formData.quantity.$touch()"
            >
              <hashtag-icon slot="leftIcon" class="h-4 ml-1 text-gray-500" />
            </sw-input>
          </sw-input-group>

            <sw-input-group
              :label="$t('movements.type')"
              :error="typeError"
              class="mb-4"
              required
            >

              <sw-select
                v-model="formData.type"
                :options="types"
                :show-labels="false"
                :allow-empty="false"
                :multiple="false"
                class="mt-2"
                track-by="val"
                label="name"
              />

            </sw-input-group>

            <div class="mb-4">
              <sw-button
                :loading="isLoading"
                variant="primary"
                size="lg"
                class="flex justify-center w-full md:w-auto"
              >
                <save-icon v-if="!isLoading" class="mr-2 -ml-1" />
                {{ isEdit ? $t('movements.update_movement') : $t('movements.save_movement') }}
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
import {
  ShoppingCartIcon,
  HashtagIcon,
} from '@vue-hero-icons/solid'
const {
  required,
} = require('vuelidate/lib/validators')

export default {
  components: {
    ShoppingCartIcon,
    HashtagIcon
  },

  data() {
    return {
      isLoading: false,
      title: 'Add Item',
      
      types : [
        {
          name: "Ingreso",
          val: "in",
        },
        {
          name: "Egreso",
          val: "out",
        }
      ],
      formData: {
        item : null,
        type: '',
        quantity: ''
      },
    }
  },

  computed: {
    ...mapGetters('company', ['defaultCurrencyForInput']),

    

    // price: {
    //   get: function () {
    //     return this.formData.price / 100
    //   },
    //   set: function (newValue) {
    //     this.formData.price = Math.round(newValue * 100)
    //   },
    // },

    pageTitle() {
      if (this.$route.name === 'movements.edit') {
        return this.$t('movements.edit_movement')
      }
      return this.$t('movements.new_movement')
    },

    // ...mapGetters('taxType', ['taxTypes']),

    isEdit() {
      if (this.$route.name === 'movements.edit') {
        return true
      }
      return false
    },

    quantityError() {
      if (!this.$v.formData.quantity.$error) {
        return ''
      }

      if (!this.$v.formData.quantity.required) {
        return this.$t('validation.quantity_required')
      }
    },    
    typeError() {
      if (!this.$v.formData.type.$error) {
        return ''
      }

      if (!this.$v.formData.type.required) {
        return this.$t('validation.type_required')
      }
    },
  },

  created() {
    this.loadData()
  },

  mounted() {
    
    this.$v.formData.$reset()
  },

  validations: {
    formData: {
      quantity: {
        required
      },
      item_id : {
        required
      },
      type: {
        required        
      },
    },
  },

  methods: {
    ...mapActions('item', [
      'addItem',
      'fetchItem',
      'updateItem',
      'fetchItemUnits',
    ]),

    ...mapActions('taxType', ['fetchTaxTypes']),

    ...mapActions('company', ['fetchCompanySettings']),

    ...mapActions('modal', ['openModal']),


    async loadData() {
      if (this.isEdit) {
        let response = await this.fetchItem(this.$route.params.id)

        this.formData = { ...response.data.item, unit: null }

        this.fractional_price = response.data.item.price

        if (this.formData.unit_id) {
          await this.fetchItemUnits({ limit: 'all' })
          this.formData.unit = this.itemUnits.find(
            (_unit) => response.data.item.unit_id === _unit.id
          )
        }

      } else {
        this.formData.item_id = this.$route.params.id;
        let response = await this.fetchItem(this.$route.params.id)
        this.formData.item = response.data.item     
      }
    },

    async submitItem() {
      this.$v.formData.$touch()

      if (this.$v.$invalid) {
        return false
      }

      if (this.formData.unit) {
        this.formData.unit_id = this.formData.unit.id
      }

      let response
      this.isLoading = true

      if (this.isEdit) {
        response = await this.updateItem(this.formData)
      } else {
        let data = {
          ...this.formData,
        }
        response = await this.addItem(data)
      }

      if (response.data) {
        this.isLoading = false

        if (!this.isEdit) {
          window.toastr['success'](this.$tc('items.created_message'))
          this.$router.push('/admin/items')
          return true
        } else {
          window.toastr['success'](this.$tc('items.updated_message'))
          this.$router.push('/admin/items')
          return true
        }
        window.toastr['error'](response.data.error)
      }
    },
  },
}
</script>
