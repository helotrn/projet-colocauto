export default {
  fr: {
    model_name: "remboursement | remboursements",
    fields: {
      amount: "montant en euros",
      user_id: "Payé par",
      user: {
        full_name: "Payé par (nom)",
        communities: {id: "Communauté"},
      },
      credited_user_id: "Payé à",
      credited_user: {
        full_name: "Payé à (nom)"
      },
      executed_at: "date",
    },
    list: {
      create: "ajouter un remboursement",
      selected: "{count} remboursement sélectionné | {count} remboursements sélectionnés",
      edit: "modifier un remboursement",
      add: "ajouter un remboursement",
    }
  }
}
