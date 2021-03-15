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
              {{ item ? item.name : "" }}
            </sw-input-group>


          <sw-input-group
            :label="$t('movements.movement_date')"
            :error="movementDateError"
            required
          >
            <base-date-picker
              v-model="formData.movement_date"
              :calendar-button="true"
              calendar-button-icon="calendar"
              class="mb-4"
              @input="$v.formData.movement_date.$touch()"
            />
          </sw-input-group>

          <sw-input-group
            :label="$t('movements.quantity') + (unit ? ' (' + unit.name + ')' : '')"
            :error="quantityError"
            class="mb-4"
            required
          >
            <sw-money
              v-model="formData.quantity"
              :invalid="$v.formData.quantity.$error"
              @input="$v.formData.quantity.$touch()"
            />
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
import moment from 'moment'
import {
  ShoppingCartIcon,
  HashtagIcon,
} from '@vue-hero-icons/solid'
const {
  required,
  minValue,
} = require('vuelidate/lib/validators')

export default {
  components: {
    ShoppingCartIcon,
    HashtagIcon
  },

  data() {
    return {
      isLoading: false,
      unit : null,
      item : null,
      formData: {        
        movement_date : moment().toString(),
        type: '',
        movement_type: '',
        quantity: 0
      },
      types : [
        {
          name: this.$t('movements.in'),
          val: "in",
        },
        {
          name: this.$t('movements.out'),
          val: "out",
        }
      ],
    }
  },

  computed: {
    ...mapGetters('company', ['defaultCurrencyForInput']),


    pageTitle() {
      if (this.$route.name === 'movements.edit') {
        return this.$t('movements.edit_movement')
      }
      return this.$t('movements.new_movement')
    },

    isEdit() {
      if (this.$route.name === 'movements.edit') {
        return true
      }
      return false
    },

    movementDateError() {
      if (!this.$v.formData.movement_date.$error) {
        return ''
      }

      if (!this.$v.formData.movement_date.required) {
        return this.$t('validation.date_required')
      }
    },
    quantityError() {
      if (!this.$v.formData.quantity.$error) {
        return ''
      }

      if (!this.$v.formData.quantity.required) {
        return this.$t('validation.quantity_required')
      }

      if (!this.$v.formData.quantity.minValue) {
        return this.$t('validation.qty_must_greater_than_zero')
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
      movement_date : {
        required
      },
      quantity: {
        required,
        minValue: minValue(0)
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
      'addItemMovement',
      'fetchItem',
      'updateItem',
    ]),

    async loadData() {
      if (this.isEdit) {
        let response = await this.fetchItem(this.$route.params.id)

        this.formData = { ...response.data.item, unit: null }


      } else {
        this.formData.item_id = this.$route.params.id;
        let response = await this.fetchItem(this.$route.params.id)
        this.item = response.data.item
        this.unit = response.data.item.unit
      }
    },

    async submitItem() {
      this.$v.formData.$touch()

      if (this.$v.$invalid) {
        return false
      }

      this.formData.movement_type = this.formData.type.val;

      let response
      this.isLoading = true

      if (this.isEdit) {
        response = await this.updateItem(this.formData)
      } else {
        let data = {
          ...this.formData,
        }
        response = await this.addItemMovement(data)
      }


      if (response.data) {
        this.isLoading = false

        if (!this.isEdit) {
          window.toastr['success'](this.$tc('movements.created_message'))
          // this.$router.push('/admin/items')
          this.$router.push({ path: '/admin/items', query: { show: this.$route.params.id } })
          return true
        } else {
          window.toastr['success'](this.$tc('movements.updated_message'))
          this.$router.push('/admin/items')
          return true
        }
        window.toastr['error'](response.data.error)
      }

    },
  },
}
</script>
