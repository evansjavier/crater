import * as types from './mutation-types'

export default {
  [types.SET_CUSTOMER_LISTS](state, customerList) {
    state.customerList = customerList
  },

  [types.SET_USER_LISTS](state, userList) {
    state.userList = userList
  },

  [types.SET_COMPANY_LISTS](state, companyList) {
    state.companyList = companyList
  },
}
