import borrowers from './borrowers';
import communities from './communities';
import loanables from './loanables';

export default {
  fr: {
    'créer un emprunt': 'créer un emprunt',
    emprunt: 'emprunt | emprunts',
    fields: {
      id: 'ID',
      borrower_id: 'emprunteur',
      borrower: borrowers.fr.fields,
      community_id: 'communauté',
      community: communities.fr.fields,
      departure_at: 'départ',
      duration_in_minutes: 'durée (minutes)',
      estimated_distance: 'kilométrage à parcourir',
      estimated_insurance: "coût estimé de l'assurance",
      estimated_price: 'coût estimé',
      loanable_id: 'véhicule',
      loanable: loanables.fr.fields,
      loanable_type: 'type de véhicule',
      message_for_owner: 'message pour le propriétaire',
      price: 'prix',
      reason: "raison de l'utilisation (ex.: épicerie, déménagement, etc.)",
      return_at: 'retour',
    },
  },
};
