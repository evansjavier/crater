import * as types from './mutation-types'

export default {
  [types.SET_COMPANIES](state, companies) {
    state.companies = companies
  },
}
