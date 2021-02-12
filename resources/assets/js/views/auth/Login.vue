<template>
<center>
<spam style="font-family: 'Museo Sans', sans-serif; font-size: 18px;">
Su sesión ha expirado. Por favor, haga 
<a href="http://proyecto2.createapps.es">click aquí</a>, para iniciar sesión.
</spam>
</center>
</template>

<script type="text/babel">
import { mapActions } from 'vuex'
import { EyeIcon, EyeOffIcon } from '@vue-hero-icons/outline'
import IconFacebook from '../../components/icon/facebook'
import IconTwitter from '../../components/icon/twitter'
import IconGoogle from '../../components/icon/google'
const { required, email, minLength } = require('vuelidate/lib/validators')

export default {
  components: {
    IconFacebook,
    IconTwitter,
    IconGoogle,
    EyeIcon,
    EyeOffIcon,
  },
  data() {
    return {
      loginData: {
        email: '',
        password: '',
        remember: '',
      },
      submitted: false,
      isLoading: false,
      isShowPassword: false,
    }
  },
  validations: {
    loginData: {
      email: {
        required,
        email,
      },
      password: {
        required,
        minLength: minLength(8),
      },
    },
  },
  computed: {
    emailError() {
      if (!this.$v.loginData.email.$error) {
        return ''
      }
      if (!this.$v.loginData.email.required) {
        return this.$tc('validation.required')
      }
      if (!this.$v.loginData.email.email) {
        return this.$tc('validation.email_incorrect')
      }
    },

    passwordError() {
      if (!this.$v.loginData.password.$error) {
        return ''
      }
      if (!this.$v.loginData.password.required) {
        return this.$tc('validation.required')
      }
      if (!this.$v.loginData.password.minLength) {
        return this.$tc(
          'validation.password_min_length',
          this.$v.loginData.password.$params.minLength.min,
          { count: this.$v.loginData.password.$params.minLength.min }
        )
      }
    },

    getInputType() {
      if (this.isShowPassword) {
        return 'text'
      }
      return 'password'
    },
  },
  methods: {
    ...mapActions('auth', ['login']),
    async validateBeforeSubmit() {
      axios.defaults.withCredentials = true

      this.$v.loginData.$touch()
      if (this.$v.$invalid) {
        return true
      }

      this.isLoading = true

      try {
        await this.login(this.loginData)
        this.$router.push('/admin/dashboard')
        this.isLoading = false
      } catch (error) {
        this.isLoading = false
      }
    },
  },
}
</script>
