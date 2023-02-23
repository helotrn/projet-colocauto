<template>
  <b-row>
    <b-col>
    <b-form-group label="Filtrer les remboursements par">
      <b-form-select :options="[{
          value: '',
          text: 'Tout afficher',
          selected: true,
        },{
          value: 'user',
          text: 'Payé par un membre du groupe',
        },{
          value: 'credited_user',
          text: 'Payé à un membre du groupe',
        },{
          value: 'date',
          text: 'Période'
        }]" v-model="filterBy" />
    </b-form-group>
    <b-form-group v-if="filterBy=='user'" label="Payé par" key="user">
      <forms-relation-input
        id="user_id"
        name="user_id"
        :query="form.user_id.query"
        placeholder="Payé par"
        @input="filter"
      />
    </b-form-group>
    <b-form-group v-else-if="filterBy=='credited_user'" label="Payé à" key="credited_user">
      <forms-relation-input
        id="credited_user_id"
        name="credited_user_id"
        :query="form.credited_user_id.query"
        placeholder="Payé à"
        @input="filter"
      />
    </b-form-group>
    <b-form-group v-else-if="filterBy=='date'" label="Dates" key="date">
      <forms-date-range-picker
        :value="dates"
        @input="dates=$event; filter($event)"
      />
    </b-form-group>
    </b-col>
  </b-row>
</template>

<script>
import FormsRelationInput from "@/components/Forms/RelationInput.vue";
import FormsDateRangePicker from "@/components/Forms/DateRangePicker.vue";
import UserMixin from "@/mixins/UserMixin";

export default {
  name: "RefundFilters",
  mixins: [UserMixin],
  components: {
    FormsRelationInput,
    FormsDateRangePicker,
  },
  data() {
    return {
      filterBy: "",
      params: {},
      dates: ":",
    }
  },
  watch: {
    filterBy() {
      this.params = {};
      if( !this.filterBy ) {
        this.params = {};
        this.dates = ":";
        this.$store.dispatch("wallet/filterRefunds", this.params);
      }
    },
  },
  computed: {
    form() {
      return this.$store.state.refunds.form;
    }
  },
  methods: {
    filter(model){
      if(!model) return;

      if( this.filterBy == 'user' ){
        this.params['user.id'] = model.id;
      } else if( this.filterBy == 'credited_user' ){
        this.params['credited_user.id'] = model.id;
      } else {
        this.params.executed_at = model;
      }
      this.$store.dispatch("wallet/filterRefunds", this.params);
    },
  },
}
</script>
