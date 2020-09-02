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
      community_id: 'Le partage de mon véhicule peut être restreint à un voisinage ou un '
        + 'quartier. Par défaut, un véhicule est accessible à tous mes voisinages.',
      instructions: 'Y a-t-il des choses à savoir sur ce véhicule?',
      location_description: 'Généralement, votre véhicule se trouve où? Cliquez sur la carte '
        + 'pour définir sa position.',
      name: "Merci de nommer votre véhicule pour en informer votre voisinage. Le nom n'a pas "
        + "besoin d'être compliqué. Allez-y au plus simple... ou au plus drôle!",
      share_with_parent_communities: 'Mon véhicule pourra être visible et demandé en réservation '
        + "par des voisin-e-s du quartier en-dehors de mon voisinage. C'est l'occasion de "
        + 'partager davantage!',
    },
    engines: cars.fr.engines,
    fields: {
      ...bikes.fr.fields,
      ...cars.fr.fields,
      ...trailers.fr.fields,
      comments: 'commentaires',
      community_id: 'voisinage',
      deleted_at: 'supprimé',
      image: 'photo du véhicule',
      instructions: 'instructions',
      location_description: "précisions sur l'emplacement",
      name: 'nom',
      owner: owners.fr.fields,
      owner_id: 'propriétaire',
      padlock_id: 'cadenas',
      position: 'position géographique',
      share_with_parent_communities: "j'accepte que mon véhicule soit partagé au quartier d'appartenance",
      share_with_parent_communities_dynamic: "j'accepte que mon véhicule soit partagé avec {shared_with}",
      type: 'type de véhicule',
      types: {
        bike: 'vélo',
        car: 'auto',
        null: "n'importe quel type",
        trailer: 'remorque',
      },
    },
    model: {
      singular: 'véhicule',
    },
    model_name: 'véhicule | véhicules',
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
    véhicule: 'véhicule | véhicules',
  },
};
