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
    <stat-chart type="invitations" />
  </div>
</template>

<script>
import StatChart from "@/components/StatChart"
import StatCard from "@/components/StatCard"

export default {
  name: "AdminDashboard",
  components: {StatChart, StatCard},
};
</script>

<style lang="scss"></style>
