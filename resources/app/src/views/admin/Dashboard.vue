<template>
  <div class="admin-dashboard">
    <h1>{{ $t("titles.dashboard") | capitalize }}</h1>

    <p>Sélectionnez une section dans le menu à gauche.</p>
    <stat-chart type="invitations" />
  </div>
</template>

<script>
import StatChart from "@/components/StatChart"

export default {
  name: "AdminDashboard",
  components: {StatChart},
};
</script>

<style lang="scss"></style>
