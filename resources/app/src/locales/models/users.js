import { prefixFields } from '@/helpers';

import communities from './communities';
import paymentMethods from './paymentMethods';

export default {
  fr: {
    descriptions: {
      description: 'Vos voisin-e-s ne vous connaissent pas encore! En quelques mots, dites-nous '
        + "qui vous êtes, on l'affichera sur votre profil. :-)",
      avatar: 'Ajoutez une photo de profil. On vous encourage à vous montrer la binette! '
        + 'Ça aide à se faire confiance et à mieux profiter des avantages du partage.'
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
      opt_in_newsletter: "j'accepte de recevoir l'infolettre LocoMotion",
      other_phone: 'autre numéro de téléphone',
      phone: 'téléphone',
      postal_code: 'code postal',
    },
    list: {
      create: 'ajouter un membre',
      selected: '{count} membre sélectionné | {count} membres sélectionnés',
    },
    model_name: 'membre | membres',
    payment_methods: paymentMethods.fr,
  },
};
