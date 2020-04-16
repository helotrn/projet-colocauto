<template>
  <b-card class="loanable-info-box shadow" bg="white" no-body>
    <router-link class="card-body" :to="`/profile/loanables/${id}`">
      <b-row>
        <b-col class="loanable-info-box__image">
          <div :style="{ backgroundImage: loanableImage }" />
        </b-col>

        <b-col class="loanable-info-box__name"><span>{{ name }}</span></b-col>

        <b-col class="loanable-info-box__actions">
          <div>
            <b-button class="ml-3 mb-3"
              size="sm" variant="outline-primary" v-if="hasButton('availability')"
              :to="`/profile/loanables/${id}#availability`">
              Modifier les disponibilités
            </b-button>

            <b-button class="ml-3 mb-3"
              size="sm" variant="outline-dark" v-if="false && hasButton('unavailable24h')"
              @click="makeLoanableUnavailableFor24h">
              Rendre indisponible (24h)
            </b-button>

            <b-button class="ml-3 mb-3"
              size="sm" variant="outline-danger" v-if="hasButton('remove')"
              @click="disableLoanableModal">
              Retirer
            </b-button>
          </div>
        </b-col>
      </b-row>
    </router-link>
  </b-card>
</template>

<script>
export default {
  name: 'LoanableInfoBox',
  props: {
    id: {
      type: Number,
      required: true,
    },
    buttons: {
      type: Array,
      required: false,
      default() {
        return ['availability', 'unavailable24h', 'remove'];
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
  },
  computed: {
    loanableImage() {
      if (!this.image) {
        return '';
      }

      return `url('${this.image.sizes.thumbnail}')`;
    },
  },
  methods: {
    async makeLoanableUnavailableFor24h() {
      await this.$store.dispatch('loanables/makeUnavailableFor24h');
      this.$bvModal.msgBoxOk('Ce véhicule est indisponible pour les prochaines 24h.', {
        buttonSize: 'sm',
        footerClass: 'd-none',
      });
    },
    disableLoanableModal() {
      this.$bvModal.msgBoxConfirm(
        'Êtes-vous sûr de vouloir retirer ce véhicule de la plateforme?',
        {
          size: 'sm',
          buttonSize: 'sm',
          okTitle: 'Oui, retirer',
          cancelTitle: 'Annuler',
          okVariant: 'danger',
          cancelVariant: 'primary',
          footerClass: 'p-2 border-top-0',
          centered: true,
        },
      )
        .then((value) => {
          if (value) {
            this.disableLoanable();
          }
        });
    },
    disableLoanable() {
      this.$store.dispatch('loanables/disable', this.id);
    },
    hasButton(name) {
      return this.buttons.indexOf(name) > -1;
    },
  },
};
</script>

<style lang="scss">
.loanable-info-box {
  a:hover, a:active, a:focus {
    text-decoration: none;
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
