export default {
  fr: {
    model_name: "dépense | dépenses",
    fields: {
      id: "ID",
      name: "Titre",
      amount: "montant en Euros",
      executed_at: "date",
      user_id: "Payé par",
      loanable_id: "Pour le véhicule",
      expense_tag_id: "Type de dépense",
      type: 'Débit/Crédit',
    },
    list: {
      create: "enregister une nouvelle dépense",
      selected: "{count} dépenses sélectionnée | {count} dépenses sélectionnées",
    }
  }
}
