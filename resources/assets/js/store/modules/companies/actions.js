import * as types from './mutation-types'
import * as searchTypes from '../search/mutation-types'

export const fetchCompanies = ({ commit, dispatch, state }, params) => {
  return new Promise((resolve, reject) => {
    window.axios
      .get(`/api/v1/companies`, { params })
      .then((response) => {
        commit(types.SET_COMPANIES, response.data.companies.data)
        commit(types.SET_TOTAL_COMPANIES, response.data.companies.total)
        commit(
          'search/' + searchTypes.SET_COMPANY_LISTS,
          response.data.companies.data,
          { root: true }
        )
        resolve(response)
      })
      .catch((err) => {
        reject(err)
      })
  })
}

export const deleteCompany = ({ commit, dispatch, state }, company) => {
  return new Promise((resolve, reject) => {
    window.axios
      .post(`/api/v1/companies/delete`, {
        companies: company,
      })
      .then((response) => {
        commit(types.DELETE_COMPANY, company)
        resolve(response)
      })
      .catch((err) => {
        reject(err)
      })
  })
}

export const deleteMultipleCompanies = ({ commit, dispatch, state }, id) => {
  return new Promise((resolve, reject) => {
    window.axios
      .post(`/api/v1/companies/delete`, { companies: state.selectedCompanies })
      .then((response) => {
        commit(types.DELETE_MULTIPLE_COMPANIES, state.selectedCompanies)
        resolve(response)
      })
      .catch((err) => {
        reject(err)
      })
  })
}

export const setSelectAllState = ({ commit, dispatch, state }, data) => {
  commit(types.SET_SELECT_ALL_STATE, data)
}

export const selectAllCompanies = ({ commit, dispatch, state }) => {
  if (state.selectedCompanies.length === state.companies.length) {
    commit(types.SET_SELECTED_COMPANIES, [])
    commit(types.SET_SELECT_ALL_STATE, false)
  } else {
    let allCompanyIds = state.companies.map((company) => company.id)
    commit(types.SET_SELECTED_COMPANIES, allCompanyIds)
    commit(types.SET_SELECT_ALL_STATE, true)
  }
}

export const selectedCompany = ({ commit, dispatch, state }, data) => {
  commit(types.SET_SELECTED_COMPANIES, data)
  if (state.selectedCompanies.length === state.companies.length) {
    commit(types.SET_SELECT_ALL_STATE, true)
  } else {
    commit(types.SET_SELECT_ALL_STATE, false)
  }
}