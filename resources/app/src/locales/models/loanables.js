import bikes from './bikes';
import cars from './cars';
import trailers from './trailers';
import owners from './owners';

export default {
  en: {
    fields: {
    },
  },
  fr: {
    bike_types: {
      cargo: 'cargo',
      electric: 'electric',
      fixed_wheel: 'roue fixe',
      regular: 'régulier',
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
      ...bikes.fr.fields,
      ...cars.fr.fields,
      ...trailers.fr.fields,
      comments: 'commentaires',
      community_id: 'communauté',
      deleted_at: 'supprimé',
      image: 'photo du véhicule',
      instructions: 'instructions',
      location_description: "précisions sur l'emplacement",
      name: 'nom',
      owner: owners.fr.fields,
      owner_id: 'propriétaire',
      padlock_id: 'cadenas',
      position: 'position géographique',
      type: 'type de véhicule',
    },
    model: {
      singular: 'véhicule',
    },
    papers_locations: {
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
