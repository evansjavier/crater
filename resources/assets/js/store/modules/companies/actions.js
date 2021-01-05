import * as types from './mutation-types'
import * as searchTypes from '../search/mutation-types'

export const fetchCompanies = ({ commit, dispatch, state }, params) => {
  return new Promise((resolve, reject) => {
    window.axios
      .get(`/api/v1/companies`, { params })
      .then((response) => {
        commit(types.SET_COMPANIES, response.data.companies)
        resolve(response)
      })
      .catch((err) => {
        reject(err)
      })
  })
}