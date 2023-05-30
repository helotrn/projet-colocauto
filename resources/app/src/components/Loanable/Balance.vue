<template>
  <div>
    Depuis que le véhicule est partagé:
    <dl v-if="loanable.stats && loanable.balance">
      <dt>Nombre d'emprunts:</dt> <dd>{{ loanable.stats.loans }}</dd>
      <dt>Kilomètres parcourus:</dt> <dd>{{ loanable.stats.km }} km</dd>
      <dt>Montant des emprunts:</dt> <dd>{{ debitLoan | currency }}</dd>
      <dt>Dépenses de carburant:</dt> <dd>{{ creditFuel | currency }}</dd>
      <dt class="diff">Différence:</dt> <dd :class="{diff: true, negative: !isLoanBalanced}">
        {{ debitLoan - creditFuel  | currency }}
      </dd>
    </dl>
    <small v-if="!isLoanBalanced">Attention, le coût au kilomètre ne couvre pas les dépenses de carburant.</small>
    <dl>
      <dt>Provisions:</dt> <dd>{{ debitFunds | currency }}</dd>
      <dt>Dépenses partagées:</dt> <dd>{{ creditShared | currency }}</dd>
      <dt class="diff">Différence:</dt> <dd :class="{diff: true, negative: !isSharedExpensesBalanded }">
        {{ debitFunds - creditShared | currency }}
      </dd>
    </dl>
    <small v-if="!isSharedExpensesBalanded">Attention, les provisions sont insuffisantes pour couvrir les dépenses partagées.</small>
  </div>
</template>

<script>
export default {
  name: "LoanableBalance",
  props: {
    loanable: Object,
    required: true,
  },
  computed: {
    debitLoan(){
      return this.loanable.balance['debit-loan'] ? parseFloat(this.loanable.balance['debit-loan'].total) : 0
    },
    debitFunds(){
      return this.loanable.balance['debit-funds'] ? parseFloat(this.loanable.balance['debit-funds'].total) : 0
    },
    creditFuel(){
      return this.loanable.balance['credit-fuel'] ? parseFloat(this.loanable.balance['credit-fuel'].total) : 0
    },
    creditShared(){
      return this.loanable.balance['credit-shared'] ? parseFloat(this.loanable.balance['credit-shared'].total) : 0
    },
    isLoanBalanced(){
      return this.debitLoan >= this.creditFuel;
    },
    isSharedExpensesBalanded(){
      return this.debitFunds >= this.creditShared;
    },
  }
}
</script>

<style scoped>
dl {
  margin-top: 1em;
}
dd {
  float: left;
  margin-left: 0.5em;
}
dt {
  float: left;
  clear: left;
}
.diff {
  margin-bottom: 2em;
}
dd.diff{
  color:#0da789;
  font-weight: bold;
}
dd.diff.negative {
  color: #e15454;
}
small {
  float: left;
  margin-bottom: 2em;
  margin-top: -1.5em;
  color: #e15454;
}
</style>
