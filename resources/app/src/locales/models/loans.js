import { prefixFields } from '@/helpers';

import borrowers from './borrowers';
import communities from './communities';
import loanables from './loanables';

export default {
  fr: {
    'créer un emprunt': 'créer un emprunt',
    descriptions: {
      platform_tip: 'LocoMotion est un projet citoyen et collaboratif. Participez à son'
        + 'développement en donnant une contribution volontaire.',
    },
    emprunt: 'emprunt | emprunts',
    fields: {
      id: 'ID',
      borrower_id: 'emprunteur',
      borrower: borrowers.fr.fields,
      community_id: 'communauté',
      community: {
        ...prefixFields(communities.fr.fields, '(Communauté)'),
        model_name: communities.fr.model_name,
      },
      departure_at: 'départ',
      duration_in_minutes: 'durée (minutes)',
      estimated_distance: 'kilométrage à parcourir',
      estimated_insurance: "coût estimé de l'assurance",
      estimated_price: 'coût estimé',
      incidents: {
        status: 'statut (Incident)',
      },
      loanable_id: loanables.fr.model.singular,
      loanable: {
        ...prefixFields(loanables.fr.fields, '(Véhicule)'),
        model_name: loanables.fr.model_name,
      },
      loanable_type: 'type de véhicule',
      message_for_owner: 'message pour le propriétaire',
      platform_tip: 'contribution volontaire LocoMotion',
      price: 'prix',
      reason: "raison de l'utilisation",
      return_at: 'retour',
    },
    incidents: {
      statuss: {
        null: '-',
        in_process: 'En cours',
        canceled: 'Annulé',
        completed: 'Complété',
      },
    },
    placeholders: {
      reason: 'ex.: épicerie, déménagement, etc.',
    },
  },
};
