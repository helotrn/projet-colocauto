<template>
  <b-row>
    <b-col sm="6">
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
    </b-col>

    <b-col sm="6" class="smaller pt-sm-4 pb-2">
      <b-form-checkbox v-model="me" switch size="lg">
        Afficher uniquement les dépenses qui me concernent
      </b-form-checkbox>
    </b-col>
  </b-row>
</template>

<script>
import FormsRelationInput from "@/components/Forms/RelationInput.vue";
import FormsDateRangePicker from "@/components/Forms/DateRangePicker.vue";
import UserMixin from "@/mixins/UserMixin";

export default {
  name: "ExpenseFilters",
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
      me: false,
    }
  },
  watch: {
    filterBy() {
      this.params = {};
      if( !this.filterBy ) {
        this.params = {};
        this.dates = ":";
        if( this.me ) this.params['user.id'] = 'me';
        this.$store.dispatch("wallet/filterExpenses", this.params);
      }
    },
    me() {
      if( this.me ){
        this.params['user.id'] = 'me';
      } else {
        delete this.params['user.id'];
      }
      this.$store.dispatch("wallet/filterExpenses", this.params);
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
        if( model.id == this.user.id ) {
          this.me = true;
        } else {
          this.me = false;
        }
      } else if( this.filterBy == 'loanable' ){
        this.params['loanable.id'] = model.id;
      } else {
        this.params.executed_at = model;
      }
      if( this.me ) this.params['user.id'] = 'me';
      this.$store.dispatch("wallet/filterExpenses", this.params);
    },
  },
}
</script>

<style scoped lang="scss">
@import "~bootstrap/scss/mixins/breakpoints";

.smaller::v-deep .custom-switch.b-custom-control-lg .custom-control-label {
  font-size: 1rem;
}

@include media-breakpoint-up(sm) {
  .smaller::v-deep .custom-switch.b-custom-control-lg .custom-control-label {
    line-height: 1;
  }
}
@include media-breakpoint-up(lg) {
  .smaller::v-deep .custom-switch.b-custom-control-lg .custom-control-label {
    line-height: 1.5;
  }
}
</style>
