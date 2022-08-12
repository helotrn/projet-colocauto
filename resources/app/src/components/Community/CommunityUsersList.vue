<template>
  <b-table striped hover :fields="fields" :items="items"> </b-table>
</template>

<script>
export default {
  name: "CommunityUsersList",
  props: {
    fields: {
      type: Array,
      required: false,
    },
    items: {
      type: [Array, Function],
      required: false,
    },
  },
};
</script>
