import { prefixFields } from '@/helpers';

import communities from './communities';
import paymentMethods from './paymentMethods';

export default {
  fr: {
    buttons: {
      add: 'ajouter un membre',
    },
    descriptions: {
      description: 'De quoi aimeriez-vous discuter avec vos voisins et voisines? En quelques'
        + 'mots, dites-nous qui vous êtes ou nommez une activité à faire dans votre quartier. '
        + 'On l\'affichera sur votre profil. :-)',
    },
    fields: {
      address: 'adresse',
      avatar: 'image de profil',
      communities: prefixFields(communities.fr.fields, '(Communauté)'),
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
    model_name: 'membre | membres',
    payment_methods: paymentMethods.fr,
  },
};
