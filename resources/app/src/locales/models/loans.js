import borrowers from './borrowers';
import loanables from './loanables';

export default {
  fr: {
    'créer un emprunt': 'créer un emprunt',
    emprunt: 'emprunt | emprunts',
    fields: {
      id: 'ID',
      borrower: borrowers.fr.fields,
      departure_at: 'départ',
      duration_in_minutes: 'durée (minutes)',
      estimated_distance: 'kilométrage à parcourir',
      estimated_price: 'prix estimé',
      loanable: loanables.fr.fields,
      loanable_type: 'type de véhicule',
      message_for_owner: 'message pour le propriétaire',
      price: 'prix',
      reason: "raison de l'emprunt (eg. épicerie, IKEA, etc.)",
      return_at: 'retour',
    },
  },
};
