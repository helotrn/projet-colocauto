<template>
  <b-card no-body class="loanable-info-box shadow" bg="white" no-body :class="{ disabled: loading }">
    <router-link :to="`/profile/loanables/${id}`">
      <b-row>
        <b-col class="loanable-info-box__image">
          <div :style="{ backgroundImage: loanableImage }" />
        </b-col>

        <b-col class="loanable-info-box__name"
          ><span>{{ name }}</span>
           <small v-if="estimated_cost">{{ estimated_cost.cost_per_km | currency }}/km</small>
           <small v-if="estimated_cost">{{ estimated_cost.cost_per_month | currency }}/mois</small>
           <small v-if="owner"><strong>Propriétaire: {{ owner.user.full_name }}</strong></small></b-col
        >
      </b-row>
    </router-link>
  </b-card>
</template>

<script>
import { extractErrors } from "@/helpers";

export default {
  name: "LoanableInfoBox",
  props: {
    id: {
      type: Number,
      required: true,
    },
    buttons: {
      type: Array,
      required: false,
      default() {
        return ["availability", "unavailable24h", "remove"];
      },
    },
    image: {
      required: false,
      type: Object,
      default: null,
    },
    name: {
      type: String,
      required: true,
    },
    owner: {
      type: Object,
      required: false,
    },
    estimated_cost: {
      type: Object,
      required: false,
    },
  },
  data() {
    return {
      loading: false,
    };
  },
  computed: {
    loanableImage() {
      if (!this.image) {
        return "";
      }

      return `url('${this.image.sizes.thumbnail}')`;
    },
  },
  methods: {
    async makeLoanableUnavailableFor24h() {
      await this.$store.dispatch("loanables/makeUnavailableFor24h");
      this.$bvModal.msgBoxOk("Ce véhicule est indisponible pour les prochaines 24h.", {
        buttonSize: "sm",
        footerClass: "d-none",
      });
    },
    disableLoanableModal() {
      this.$bvModal
        .msgBoxConfirm("Êtes-vous sûr de vouloir retirer ce véhicule de la plateforme?", {
          size: "sm",
          buttonSize: "sm",
          okTitle: "Oui, retirer",
          cancelTitle: "Annuler",
          okVariant: "danger",
          cancelVariant: "primary",
          footerClass: "p-2 border-top-0",
          centered: true,
        })
        .then((value) => {
          if (value) {
            this.disableLoanable();
          }
        });
    },
    async disableLoanable() {
      this.loading = true;
      try {
        await this.$store.dispatch("loanables/disable", this.id);
        this.$emit("disabled", this.id);
      } catch (e) {
        if (e.request?.status === 422) {
          this.$store.commit("addNotification", {
            content: extractErrors(e.response.data).join(", "),
            title: "Impossible de retirer le véhicule",
            variant: "danger",
          });
        } else {
          throw e;
        }
      } finally {
        this.loading = false;
      }
    },
    hasButton(name) {
      return this.buttons.indexOf(name) > -1;
    },
  },
};
</script>

<style lang="scss">
.loanable-info-box {
  a:hover,
  a:active,
  a:focus {
    text-decoration: none;
  }

  &.card {
    border-radius: 10px;
  }

  &.disabled {
    opacity: 0.5;
    pointer-events: none;
  }

  &__image.col {
    height: 103px;
    flex-grow: 0;
    flex-basis: 118px;
    padding-right: 0;

    > div {
      height: 103px;
      width: 103px;
      background-size: cover;
      background-repeat: no-repeat;
      background-position: center center;
      border-radius: 10px 0 0 10px;
    }
  }

  &__name.col {
    flex-grow: 1;
    color: $black;
    font-size: 18px;
    strong {
      font-weight: 600;
      line-height: 3;
    }
  }

  &__name.col,
  &__actions.col {
    display: flex;
    flex-direction: column;
    justify-content: center;
  }

  &__actions.col {
    flex: 1 1 0;
    text-align: right;
  }
}
</style>
