import communities from './communities';

export default {
  fr: {
    'ajouter un membre': 'ajouter un membre',
    descriptions: {
      description: 'De quoi aimeriez-vous discuter avec vos voisins et voisines? En quelques'
        + 'mots, dites-nous qui vous êtes ou nommez une activité à faire dans votre quartier. '
        + 'On l\'affichera sur votre profil. :-)',
    },
    membre: 'membre | membres',
    fields: {
      address: 'adresse',
      avatar: 'image de profil',
      communities: Object.keys(communities.fr.fields).reduce((acc, f) => {
        acc[f] = `${communities.fr.fields[f]} (Communauté)`;
        return acc;
      }, {}),
      date_of_birth: 'date de naissance',
      deleted_at: 'supprimé',
      description: 'description',
      email: 'courriel',
      full_name: 'nom complet',
      id: 'ID',
      is_smart_phone: 'téléphone intelligent?',
      last_name: 'nom',
      name: 'prénom',
      other_phone: 'autre numéro de téléphone',
      phone: 'téléphone',
      postal_code: 'code postal',
    },
  },
};
