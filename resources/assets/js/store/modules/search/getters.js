export const getCustomerList = (state) =>
  state.customerList ? state.customerList : []

export const getUserList = (state) => (state.userList ? state.userList : [])

export const getCompanyList = (state) => (state.companyList ? state.companyList : [])