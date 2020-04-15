import cars from './cars';
import owners from './owners';

export default {
  en: {
    fields: {
    },
  },
  fr: {
    bike_types: {
      regular: 'régulier',
      electric: 'electric',
      fixed_wheel: 'roue fixe',
    },
    'créer un véhicule': 'créer un véhicule',
    '{count} véhicule sélectionné': 'aucun véhicule sélectionné | 1 véhicule sélectionné | {count} véhicules sélectionnés',
    engines: cars.fr.engines,
    fields: {
      id: 'ID',
      bike_type: 'type de vélo',
      brand: 'marque',
      comments: 'commentaires',
      community_id: 'communauté',
      deleted_at: 'supprimé',
      engine: 'moteur',
      has_accident_report: "un rapport d'accident exists-t-il?",
      has_informed_insurer: "l'assureur a-t-il été informé?",
      image: 'photo',
      instructions: 'instructions',
      insurer: 'assureur',
      is_value_over_fifty_thousand: 'la valeur de ce véhicule dépasse-t-elle 50 000$?',
      location_description: "précisions sur l'emplacement",
      maximum_charge: 'charge maximale',
      model: 'modèle',
      name: 'nom',
      owner: owners.fr.fields,
      ownership: 'type de propriété',
      owner_id: 'propriétaire',
      padlock_id: 'cadenas',
      papers_location: 'emplacement des papiers',
      plate_number: 'numéro de plaque',
      position: 'position géographique',
      size: 'taille',
      transmission_mode: 'transmission',
      type: 'type',
      year_of_circulation: 'année de circulation',
    },
    model: {
      singular: 'véhicule',
    },
    papers_location: {
      in_the_car: 'dans la voiture',
      to_request_with_car: 'à récupérer avec la voiture',
    },
    sizes: {
      big: 'grand',
      medium: 'moyen',
      small: 'petit',
      kid: 'enfant',
    },
    transmission_modes: cars.fr.transmission_modes,
    types: {
      bike: 'vélo',
      car: 'voiture',
      null: "n'importe quel type",
      trailer: 'remorque',
    },
    véhicule: 'véhicule | véhicules',
  },
};
