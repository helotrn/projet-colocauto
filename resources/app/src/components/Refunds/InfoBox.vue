<template>
  <b-card no-body class="refund-info-box shadow my-2" bg="white" no-body :class="{ disabled: loading }">
    <router-link :to="`/wallet/refunds/${id}`">
      <div class="card-header">
          <b-row>
            <b-col><span class="small" v-if="executed_at">{{ new Date(executed_at).toLocaleDateString() }}</span></b-col>
            <b-col class="text-right"><icons-edit class="svgicon action" /></b-col>
          </b-row>
      </div>
      <div class="card-body">
        <b-row>
          <b-col>
            <span class="big">de {{ user.full_name }} à {{ credited_user.full_name }}</span>
          </b-col>
          <b-col class="text-right">
            <strong class="huge">{{ amount | currency }}</strong>
          </b-col>
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
import IconsEdit from "@/assets/icons/edit.svg";

export default {
  name: "RefundInfoBox",
  props: {
    id: {
      type: Number,
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
    credited_user: {
      type: Object,
      required: true,
    },
    changes: {
      type: Array,
      required: false,
      default: [],
    },
  },
  data() {
    return {
      loading: false,
    };
  },
  components: {
    IconsEdit,
  }
}
</script>

<style lang="scss">
.refund-info-box {
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
