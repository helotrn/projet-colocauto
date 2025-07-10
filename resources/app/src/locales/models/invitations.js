export default {
  fr: {
    invitation: "invitation | invitations",
    model_name: "invitation | invitations",
    "créer une invitation": "créer une invitation",
    fields: {
      id: "ID",
      email: "Envoyer une invitation par email",
      for_community_admin: "Donner le droit d'administration de communauté",
      community_id: "communauté dans laquelle sera intégrée le nouveau membre",
      community: {
        id: 'communauté'
      },
      token: "code (créé automatiquement)",
      consumed_at: "date d'utilisation (rempli automatiquement)",
      user: {id: 'Utilisateur'},
      created_at: 'Envoyée le',
      consumed_at: 'Consomée le',
    },
    placeholders: {
      email: 'Couriel',
    },
    list: {
      create: "créer une invitation",
      selected: "{count} invitation sélectionnée | {count} invitations sélectionnées",
      status: "Statut",
    },
  },
};
