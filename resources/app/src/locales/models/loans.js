import { prefixFields } from '@/helpers';

import borrowers from './borrowers';
import communities from './communities';
import loanables from './loanables';

const statuss = {
  null: '-',
  in_process: 'En cours',
  canceled: 'Annulé',
  completed: 'Complété',
};

export default {
  fr: {
    'créer un emprunt': 'créer un emprunt',
    current_steps: {
      null: '-',
      extension: 'extension',
      handover: 'remise du véhicule',
      incident: 'incident',
      intention: 'intention',
      payment: 'conclusion',
      pre_payment: 'prépaiement',
      takeover: 'prise de possession',
    },
    descriptions: {
      platform_tip: 'LocoMotion est un projet citoyen et collaboratif. Participez à son'
        + 'fonctionnement en donnant une contribution volontaire.',
    },
    fields: {
      id: 'ID',
      borrower_id: 'emprunteur',
      borrower: borrowers.fr.fields,
      calendar_days: 'jours calendaires',
      community_id: 'communauté',
      community: {
        ...prefixFields(communities.fr.fields, '(Communauté)'),
        model_name: communities.fr.model_name,
      },
      current_step: 'étape en cours',
      departure_at: 'départ',
      duration_in_minutes: 'durée (minutes)',
      estimated_distance: 'kilométrage à parcourir',
      estimated_insurance: "coût estimé de l'assurance",
      estimated_price: 'coût estimé',
      incidents: {
        status: 'statut (Incident)',
        statuss, // FIXME
      },
      loanable_id: loanables.fr.model.singular,
      loanable: {
        ...prefixFields(loanables.fr.fields, '(Véhicule)'),
        model_name: loanables.fr.model_name,
      },
      loan_status: "statut de l'emprunt",
      loan_statuss: statuss,
      loanable_type: 'type de véhicule',
      message_for_owner: 'message pour le propriétaire',
      platform_tip: 'contribution volontaire LocoMotion',
      price: 'prix',
      reason: "raison de l'utilisation",
      return_at: 'retour',
      takeover: {
        status: 'statut (Prise de possession)',
        statuss, // FIXME
      },
    },
    model: {
      singular: 'emprunt',
    },
    model_name: 'emprunt | emprunts',
    placeholders: {
      reason: 'ex.: épicerie, déménagement, etc.',
    },
  },
};
