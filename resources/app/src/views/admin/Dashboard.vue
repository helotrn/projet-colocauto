<template>
  <div class="admin-dashboard">
    <h1>{{ $t("titles.dashboard") | capitalize }}</h1>

    <h2>Les chiffres clés de vos communautés Coloc'Auto 🔎</h2>

    <b-row class="mb-4">
      <b-col sm="6" md="4" lg="3" class="mb-2">
        <stat-card type="loanables" title="Voitures partagées" />
      </b-col>
      <b-col sm="6" md="4" lg="3" class="mb-2">
        <stat-card type="users" title="Utilisateur·ices" />
      </b-col>
      <b-col sm="6" md="4" lg="3" class="mb-2">
        <stat-card type="communities" title="Groupes d'autopartage" />
      </b-col>
      <b-col sm="6" md="4" lg="3" class="mb-2">
        <stat-card type="loans" title="Total des emprunts réalisés 🚗" />
      </b-col>
    </b-row>
    <stat-chart type="communities" title="Nombre de participant.e.s par groupe" class="mb-4" />
    <stat-chart type="invitations" title="Invitations par semaine" class="mb-4" />

    <h2>Zoom sur les emprunts 🏁</h2>
    <stat-chart type="loans" title="Nombre d'emprunt par statuts et par mois" class="mb-4" />
  </div>
</template>

<script>
import StatChart from "@/components/StatChart"
import StatCard from "@/components/StatCard"

export default {
  name: "AdminDashboard",
  components: {StatChart, StatCard},
  methods: {
    resetParams(slug){
      Object.keys(this.$store.state[slug].params).forEach(param => {
        if( !['page', 'per_page', 'q', 'order'].includes(param) ){
          this.$store.commit(`${slug}/setParam`, { name: param, value: undefined });
        }
      })
    }
  },
  mounted(){
    this.resetParams('invitations');
    this.$store.dispatch('invitations/retrieve', {
      order: 'created_at',
      per_page: -1,
      created_at: this.$dayjs().startOfDay().subtract(5, "week").format('YYYY-MM-DDTHH:mm:ss[Z]')+'@'
    });

    this.resetParams('users');
    this.$store.dispatch('users/retrieve', {
      fields: 'id',
    });

    this.resetParams('loanables');
    this.$store.dispatch('loanables/retrieve', {
      fields: 'id',
    });

    this.resetParams('loans');
    this.$store.dispatch('loans/retrieve', {
      order: 'departure_at',
      per_page: -1,
      fields: 'id, departure_at, status, takeover.status, community.id, community.name',
    });

    this.resetParams('communities');
    this.$store.dispatch('communities/retrieve', {
      order: '-approved_users_count',
      per_page: -1,
      fields: 'id, name, approved_users_count',
    });
  }
};
</script>

<style lang="scss"></style>
