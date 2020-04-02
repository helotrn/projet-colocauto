<template>
  <div class="loan-header">
    <b-row>
      <b-col>
        <h1>Emprunt {{ loanablePrettyName }}</h1>
      </b-col>
    </b-row>

    <b-row>
      <b-col class="loan-header__description">
        <p>
        {{ prettyType }} {{ loanableDescription }} {{ loanableOwnerText }}
        <br>
        {{ loan.departure_at | day | capitalize }} {{ loan.departure_at | date }}
        &bull;
        {{ loan.departure_at | time }} à {{ returnAt | time }}
        </p>
      </b-col>
    </b-row>
  </div>
</template>

<script>
export default {
  name: 'LoanHeader',
  props: {
    loan: {
      type: Object,
      required: true,
    },
    user: {
      type: Object,
      required: true,
    },
  },
  computed: {
    loanableDescription() {
      switch (this.loan.loanable.type) {
        case 'car':
          return `${this.loan.loanable.brand} ${this.loan.loanable.model} `
            + `${this.loan.loanable.year_of_circulation}`;
        case 'bike':
          return `${this.loan.loanable.model}`;
        case 'trailer':
          return '';
        default:
          return '';
      }
    },
    loanableOwnerText() {
      if (this.userIsOwner) {
        return '';
      }

      const ownerName = this.loan.loanable.owner.user.name;
      const particle = ['a', 'e', 'i', 'o', 'u', 'é', 'è']
        .indexOf(ownerName[0]
          .toLowerCase()) > -1 ? "d'" : 'de ';
      return `${particle}${ownerName}`;
    },
    loanablePrettyName() {
      let particle;
      let type;

      switch (this.loan.loanable.type) {
        case 'car':
          particle = 'de la ';
          type = 'voiture';
          break;
        case 'bike':
          particle = 'du ';
          type = 'vélo';
          break;
        case 'trailer':
          particle = 'de la ';
          type = 'remorque';
          break;
        default:
          particle = "de l'";
          type = 'objet';
          break;
      }

      if (this.user.id === this.loan.loanable.owner.user.id) {
        particle = 'de votre ';
      }

      const description = `${particle}${type}`;
      return `${description} ${this.loanableOwnerText}`;
    },
    prettyType() {
      switch (this.loan.loanable.type) {
        case 'car':
          return 'Voiture';
        case 'bike':
          return 'Vélo';
        case 'trailer':
          return 'Remorque';
        default:
          return 'Objet';
      }
    },
    returnAt() {
      return this.$dayjs(this.loan.departure_at)
        .add(this.loan.duration_in_minutes, 'minute')
        .format('YYYY-MM-DD HH:mm:ss');
    },
    userIsOwner() {
      return this.user.id === this.loan.loanable.owner.user.id;
    },
  },
};
</script>

<style lang="scss">
.loan-header {
  h1 {
    margin-bottom: 30px;
  }

  &__description {
    font-size: 20px;
    font-weight: 600;
    line-height: 30px;
    margin-bottom: 30px;
  }
}
</style>
