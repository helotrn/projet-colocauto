<template>
  <div>
    Depuis que le véhicule est partagé:
    <dl v-if="loanable.stats && loanable.balance">
      <dt>Nombre d'emprunts:</dt> <dd>{{ loanable.stats.loans }}</dd>
      <dt>Kilomètres parcourus:</dt> <dd>{{ loanable.stats.km }} km</dd>
      <dt>Montant des emprunts:</dt> <dd>{{ loanable.balance['debit-loan'].total | currency }}</dd>
      <dt>Dépenses de carburant:</dt> <dd>{{ loanable.balance['credit-fuel'].total | currency }}</dd>
      <dt class="diff">Différence:</dt> <dd :class="{diff: true, negative: !isLoanBalanced}">
        {{ parseFloat(loanable.balance['debit-loan'].total) - parseFloat(loanable.balance['credit-fuel'].total)  | currency }}
      </dd>
    </dl>
    <small v-if="!isLoanBalanced">Attention, le coût au kilomètre ne couvre pas les dépenses de carburant.</small>
    <dl>
      <dt>Provisions:</dt> <dd>{{ loanable.balance['debit-funds'].total | currency }}</dd>
      <dt>Dépenses partagées:</dt> <dd>{{ loanable.balance['credit-shared'].total | currency }}</dd>
      <dt class="diff">Différence:</dt> <dd :class="{diff: true, negative: !isSharedExpensesBalanded }">
        {{ parseFloat(loanable.balance['debit-funds'].total) - parseFloat(loanable.balance['credit-shared'].total) | currency }}
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
    isLoanBalanced(){
      return parseFloat(this.loanable.balance['debit-loan'].total) >= parseFloat(this.loanable.balance['credit-fuel'].total);
    },
    isSharedExpensesBalanded(){
      return parseFloat(this.loanable.balance['debit-funds'].total) >= parseFloat(this.loanable.balance['credit-shared'].total);
    }
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
