<template>
  <b-card no-body
    class="expense-info-box my-2"
    :class="{ disabled: loading, 'shadow-sm': user.id != me, shadow: user.id == me }"
    :style="{backgroundColor: user.id == me ? 'white' : '#FFFFFF99'}"
    >
    <router-link :to="`/wallet/expenses/${id}`">
      <div class="card-header">
          <b-row>
            <b-col>
              <span v-if="tag" class="badge" :class="`badge-${tag.color}`">
                {{tag.name}}
              </span>
            </b-col>
            <b-col class="text-right">
              <icons-recurring v-if="locked" style="width:25px" class="svgicon action" />
              <icons-edit v-else class="svgicon action" />
            </b-col>
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
            <strong class="huge">{{ amount | currency }}</strong>
          </b-col>
        </b-row>
        <b-row style="line-height: 1.3em">
          <b-col class="small mt-3"><icons-car class="svgicon" /> {{ loanable.name }}</b-col>
        </b-row>
      </div>
    </router-link>
    <div class="card-footer text-right" v-if="changes.length">
      <span class="badge badge-warning small">{{changes.length}} modification{{changes.length > 1 ? 's' : ''}} effectuée{{changes.length > 1 ? 's' : ''}}</span>
    </div>
  </b-card>
</template>

<script>
import { extractErrors } from "@/helpers";
import IconsCar from "@/assets/icons/car.svg";
import IconsEdit from "@/assets/icons/edit.svg";
import IconsRecurring from "@/assets/icons/recurring.svg";

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
    tag: {
      type: Object,
      required: false,
    },
    changes: {
      type: Array,
      required: false,
      default: [],
    },
    locked: {
      type: Boolean,
      required: false,
      default: false,
    },
  },
  data() {
    return {
      loading: false,
    };
  },
  computed: {
    me() {
      return this.$store.state.user.id;
    }
  },
  components: {
    IconsCar,
    IconsEdit,
    IconsRecurring,
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
  .badge-success {
    background-color: #E5F5F0;
    color: #34A853;
  }
  .badge-danger {
    background-color: #f5e5e5;
    color: #a83434;
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
