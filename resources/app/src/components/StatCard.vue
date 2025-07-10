<template>
  <b-card class="text-center">
    <b-card-title>
      <big class="d-block" v-if="!loading">{{value}}</big>
      <layout-loading v-else />
      {{title}}
    </b-card-title>
  </b-card>
</template>

<script>
export default {
  name: "StatCard",
  props: {
    type: {
      type: String,
      required: true,
    },
    title: {
      type: String,
      required: true,
    }
  },
  mounted(){
    this.$store.dispatch(`${this.type}/retrieve`, {
      order: 'created_at',
      per_page: -1,
      fields: 'id',
    });
  },
  computed: {
    value(){
      return this.$store.state[this.type].data.length
    },
    loading(){
      return !this.$store.state[this.type].loaded
    }
  }
}
</script>
<style scoped>
big {
  font-size: 3em;
  line-height: 1;
  font-weight: bold;
}
.layout-loading {
  height: 3em
}
</style>
