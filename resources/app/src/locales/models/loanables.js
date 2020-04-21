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
    descriptions: {
      location_description: 'Généralement, votre véhicule se trouve où? Cliquez sur la carte '
        + 'pour définir sa position.',
      name: "Merci de nommer votre véhicule pour en informer votre voisinage. Le nom n'a pas "
        + "besoin d'être compliqué. Allez-y au plus simple... ou au plus drôle !",
    },
    engines: cars.fr.engines,
    fields: {
      id: 'ID',
      bike_type: 'type de vélo',
      brand: 'marque',
      comments: 'commentaires',
      community_id: 'communauté',
      deleted_at: 'supprimé',
      engine: 'moteur',
      has_accident_report: "un rapport d'accident existe-t-il?",
      has_informed_insurer: "l'assureur a-t-il été informé?",
      image: 'photo du véhicule',
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
      type: 'type de véhicule',
      year_of_circulation: 'année de circulation',
    },
    model: {
      singular: 'véhicule',
    },
    papers_location: {
      in_the_car: 'dans la voiture',
      to_request_with_car: 'à récupérer avec la voiture',
    },
    placeholders: {
      comments: "ex.: J'ai un siège pour bébé et un support pour les vélos en arrière.",
      instructions: "ex.: SVP ne pas fumer dans mon auto. Aussi, j'apprécierai que le siège de "
        + "bébé soit replacé, s'il y a lieu.  Merci beaucoup!",
      location_description: 'ex.: Généralement dans la ruelle, textez-moi plus plus de '
        + 'précision',
      name: 'ex.: la LocoMobile bleue',
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
      car: 'auto',
      null: "n'importe quel type",
      trailer: 'remorque',
    },
    véhicule: 'véhicule | véhicules',
  },
};
