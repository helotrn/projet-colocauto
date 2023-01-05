<template>
  <b-card no-body class="expense-info-box shadow my-2" bg="white" no-body :class="{ disabled: loading }">
    <router-link :to="`/wallet/expenses/${id}`">
      <div class="card-header">
          <b-row>
            <b-col><span class="badge badge-primary">Emprunt</span></b-col>
            <b-col class="text-right"><icons-edit class="svgicon action" /></b-col>
          </b-row>
          <b-row>
            <b-col><span class="small" v-if="executed_at">{{ new Date(executed_at).toLocaleDateString() }}</span></b-col>
          </b-row>
      </div>
      <div class="card-body">
        <b-row>
          <b-col>
            <strong class="big">{{ name }}</strong><br/>
            <span class="small">par {{ user.full_name }}</span>
          </b-col>
          <b-col class="text-right">
            <strong class="huge">{{ amount }} €</strong>
          </b-col>
        </b-row>
        <b-row style="line-height: 1.3em">
          <b-col class="small mt-3"><icons-car class="svgicon" /> {{ loanable.name }}</b-col>
        </b-row>
      </div>
    </router-link>
    <div class="card-footer text-right">
      <span class="badge badge-warning small">2 modifications effectuées</span>
    </div>
  </b-card>
</template>

<script>
import { extractErrors } from "@/helpers";
import IconsCar from "@/assets/icons/car.svg";
import IconsEdit from "@/assets/icons/edit.svg";

export default {
  name: "ExpenseInfoBox",
  props: {
    id: {
      type: Number,
      required: true,
    },
    name: {
      type: String,
      required: true,
    },
    amount: {
      type: String,
      required: true,
    },
    executed_at: {
      type: String,
      required: false,
    },
    user: {
      type: Object,
      required: true,
    },
    loanable: {
      type: Object,
      required: true,
    },
  },
  data() {
    return {
      loading: false,
    };
  },
  components: {
    IconsCar,
    IconsEdit,
  }
}
</script>

<style lang="scss">
.expense-info-box {
  a,
  a:hover,
  a:active,
  a:focus {
    text-decoration: none;
    color: black;
  }

  .card-header, .card-footer {
    background: transparent;
  }
  .card-header {
    padding-top: 20px;
    padding-bottom: 5px;
  }
  .card-body {
    padding-top: 5px;
  }

  .svgicon {
    padding: 0.15em;
    width: 1.3em;
    height: 1.3em;
    vertical-align: bottom;
  }

  a .action {
    fill: #A9AFB5;
  }
  a:hover .action {
    fill: $primary;
  }

  .badge {
    font-size: 100%;
    font-weight: normal;
    border-radius: 2px;
  }
  .badge-primary {
    background-color: #DFEAFF;
    color: #246AEA;
  }
  .badge-warning {
    background-color: #FFEFC8;
    color: #7E4C02;
  }
  .small {
    font-size: 87.5%;
  }
  .big {
    font-size: 112.5%;
  }
  .huge {
    font-size: 137.5%;
  }
}
</style>
