<template>
  <div class="user-add-credit-box">
    <b-row>
      <b-col class="user-add-credit-box__add">
        <p>Ajouter</p>

        <p><b-form-radio-group v-model="selectedOption" :options="options" buttons /></p>

        <p v-if="selectedOption === 'other'" class="user-add-credit-box__add__custom">
          <b-form-input v-model="customAmount" />
        </p>

        <p class="user-add-credit-box__balance__cost">
          coûtera {{ (amount * fee + feeConstant) | currency }} avec les frais.
        </p>

        <p>
          <em>Frais de transaction : 5% + 1$</em>
        </p>
      </b-col>

      <b-col class="user-add-credit-box__balance">
        <p>Vous avez actuellement</p>

        <p class="user-add-credit-box__balance__initial">{{ user.balance | currency }}</p>

        <p>
          {{ user.balance > 1 ? 'auquel' : 'auxquels' }} on ajoute {{ amount | currency }}
          pour un nouveau total de
        </p>

        <p class="user-add-credit-box__balance__added">{{ newAmount | currency }}</p>
      </b-col>
    </b-row>

    <b-row class="user-add-credit-box__explanations">
      <b-col>
        <p>
          Pour limiter au maximum l'impact des frais de transaction, envisagez de vous procurer
          plus de crédits Locomotion à la fois.
        </p>
      </b-col>
    </b-row>

    <b-row class="user-add-credit-box__buttons">
      <b-col class="text-center">
        <b-button class="mr-3" type="submit" variant="primary" @click="buyCredit"
          :disabled="amount < minimumRequired">
          Acheter
        </b-button>

        <b-button variant="danger" @click="emitCancel">Annuler</b-button>
      </b-col>
    </b-row>
  </div>
</template>

<script>
export default {
  name: 'UserAddCreditBox',
  data() {
    return {
      customAmount: this.minimumRequired * 2,
      fee: 1.05,
      feeConstant: 1,
      selectedOption: parseFloat(this.minimumRequired, 10),
    };
  },
  props: {
    minimumRequired: {
      type: String,
      required: true,
    },
    user: {
      type: Object,
      required: true,
    },
  },
  computed: {
    amount() {
      if (this.selectedOption === 'other') {
        return parseFloat(this.customAmount, 10);
      }

      return parseFloat(this.selectedOption, 10);
    },
    newAmount() {
      return this.amount + parseFloat(this.user.balance);
    },
    options() {
      const options = [
        {
          text: 'Minimum requis',
          value: parseFloat(this.minimumRequired, 10),
        },
      ];

      const standardOptions = [
        {
          text: '10$',
          value: 10,
        },
        {
          text: '20$',
          value: 20,
        },
        {
          text: '50$',
          value: 50,
        },
        {
          text: '100$',
          value: 100,
        },
      ];

      for (let i = 0, len = standardOptions.length; i < len; i += 1) {
        if (standardOptions[i].value > this.minimumRequired) {
          options.push(standardOptions[i]);
        }
      }

      options.push({
        text: 'Autre',
        value: 'other',
      });

      return options;
    },
  },
  methods: {
    emitCancel() {
      this.$emit('cancel');
    },
    async buyCredit() {
      await this.$store.dispatch('account/buyCredit', this.amount);
    },
  },
};
</script>

<style lang="scss">
.user-add-credit-box {
  &__add label {
    display: flex;
    flex-direction: column;
    justify-content: center;
  }

  &__add__custom {
    margin-right: 70px;
    margin-left: 70px;
    text-align: center;
  }

  &__balance {
    &__initial, &__added {
      font-size: 24px;
      font-weight: bold;
    }
  }
}
</style>
