<template>
  <base-page>
    <dashboard-stats />
    <dashboard-chart />
    <dashboard-table />
  </base-page>
</template>

<script>
import { mapGetters } from 'vuex'
import DashboardStats from '../dashboard/DashboardStats'
import DashboardChart from '../dashboard/DashboardChart'
import DashboardTable from '../dashboard/DashboardTable'

export default {
  components: {
    DashboardStats,
    DashboardChart,
    DashboardTable,
  },

  computed: {
    ...mapGetters('user', ['currentUser']),

    isSuperAdmin() {
      return this.currentUser.role == 'super admin'
    },    
  },
  created() {
    if (this.isSuperAdmin) {
      this.$router.push('/admin/users')
    }
  },
}
</script>
