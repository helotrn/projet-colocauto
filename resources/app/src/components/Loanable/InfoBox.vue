<template>
  <b-card class="loanable-info-box shadow" bg="white" no-body :class="{ disabled: loading }">
    <router-link class="card-body" :to="`/profile/loanables/${id}`">
      <b-row>
        <b-col class="loanable-info-box__image">
          <div :style="{ backgroundImage: loanableImage }" />
        </b-col>

        <b-col class="loanable-info-box__name"
          ><span>{{ name }}</span> <span v-if="owner">({{ owner.user.full_name }})</span></b-col
        >

        <b-col class="loanable-info-box__actions">
          <div>
            <b-button
              class="ml-3 mb-3"
              size="sm"
              :disabled="loading"
              variant="outline-primary"
              v-if="hasButton('availability')"
              :to="`/profile/loanables/${id}#availability`"
            >
              Modifier les disponibilités
            </b-button>

            <b-button
              class="ml-3 mb-3"
              size="sm"
              :disabled="loading"
              variant="outline-dark"
              v-if="false && hasButton('unavailable24h')"
              @click.prevent="makeLoanableUnavailableFor24h"
            >
              Rendre indisponible (24h)
            </b-button>

            <b-button
              class="ml-3 mb-3"
              size="sm"
              :disabled="loading"
              variant="outline-danger"
              v-if="hasButton('remove')"
              @click.prevent="disableLoanableModal"
            >
              Retirer
            </b-button>
          </div>
        </b-col>
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

  &.disabled {
    opacity: 0.5;
    pointer-events: none;
  }

  &__image.col {
    flex: 0 1 115px;
    height: 85px;

    > div {
      height: 85px;
      width: 85px;
      background-size: cover;
      background-repeat: no-repeat;
      background-position: center center;
      border-radius: 100%;
    }
  }

  &__name.col {
    flex-grow: 1;
    color: $black;
    font-size: 20px;
  }

  &__name.col,
  &__actions.col {
    display: flex;
    flex-direction: column;
    justify-content: space-around;
  }

  &__actions.col {
    flex: 1 1 0;
    text-align: right;
  }
}
</style>
