<template>
  <div>
    <b-form-group label="Filtrer les dépenses par">
      <b-form-select :options="[{
          value: '',
          text: 'Tout afficher',
          selected: true,
        },{
          value: 'user',
          text: 'Membre du groupe',
        },{
          value: 'loanable',
          text: 'Véhicule',
        },{
          value: 'date',
          text: 'Période'
        }]" v-model="filterBy" />
    </b-form-group>
    <b-form-group v-if="filterBy=='user'" label="Activité de" key="user">
      <forms-relation-input
        id="user_id"
        name="user_id"
        :query="form.user_id.query"
        placeholder="Membre du groupe"
        @input="filter"
      />
    </b-form-group>
    <b-form-group v-else-if="filterBy=='loanable'" label="Véhicule" key="loanable">
      <forms-relation-input
        id="loanable_id"
        name="loanable_id"
        :query="form.loanable_id.query"
        placeholder="Véhicule"
        @input="filter"
      />
    </b-form-group>
    <b-form-group v-else-if="filterBy=='date'" label="Dates" key="date">
      <forms-date-range-picker
        :value="dates"
        @input="dates=$event; filter($event)"
      />
    </b-form-group>
  </div>
</template>

<script>
import FormsRelationInput from "@/components/Forms/RelationInput.vue";
import FormsDateRangePicker from "@/components/Forms/DateRangePicker.vue";

export default {
  name: "ExpenseFilters",
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
      if( !this.filterBy ) this.$store.dispatch("wallet/filterExpenses", {});
    }
  },
  computed: {
    form() {
      return this.$store.state.expenses.form;
    }
  },
  methods: {
    filter(model){
      if(!model) return;

      if( this.filterBy == 'user' ){
        this.params['user.id'] = model.id;
      } else if( this.filterBy == 'loanable' ){
        this.params['loanable.id'] = model.id;
      } else {
        this.params.executed_at = model;
      }
      this.$store.dispatch("wallet/filterExpenses", this.params);
    },
  },
}
</script>
