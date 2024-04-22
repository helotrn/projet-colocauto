import prefixFields from "@/locales/prefixFields";
import bikes from "./bikes";
import cars from "./cars";
import trailers from "./trailers";
import owners from "./owners";
import communities from "./communities";

export default {
  en: {
    fields: {},
  },
  fr: {
    bike_types: {
      cargo: "cargo",
      electric: "electric",
      fixed_wheel: "roue fixe",
      regular: "régulier",
    },
    "créer un véhicule": "créer un véhicule",
    "{count} véhicule sélectionné":
      "aucun véhicule sélectionné | 1 véhicule sélectionné | {count} véhicules sélectionnés",
    descriptions: {
      comments: "Quoi savoir sur ce véhicule avant de faire l'emprunt? Où se trouve-t-il généralement ?",
      community_id:
        "Le partage de mon véhicule peut être restreint à un voisinage ou un " +
        "quartier. Par défaut, un véhicule est accessible à tous mes voisinages.",
      image:
        "L'image de votre véhicule s'affichera dans un ratio d'aspect de 16 par 10. Assurez-vous qu'il est bien visible dans l'aperçu ici.",
      instructions:
        "Quoi à savoir sur l'utilisation de ce véhicule? (accessible à l'emprunteur seulement)",
      location_description:
        "Généralement, votre véhicule se trouve où ?",
      name:
        "Merci de nommer votre véhicule pour en informer votre voisinage. Le nom n'a pas " +
        "besoin d'être compliqué. Allez-y au plus simple... ou au plus drôle!",
      share_with_parent_communities:
        "Mon véhicule pourra être visible et demandé en réservation " +
        "par des voisin-e-s du quartier en-dehors de mon voisinage. C'est l'occasion de " +
        "partager davantage!",
      is_self_service:
        "Mon véhicule sera accessible en mode libre service, c'est-à-dire que" +
        " les demandes d'emprunts seront automatiquement acceptées.",
    },
    engines: cars.fr.engines,
    fields: {
      ...bikes.fr.fields,
      ...cars.fr.fields,
      ...trailers.fr.fields,
      comments: "commentaires",
      community_id: "voisinage",
      community_name: "voisinage",
      community: prefixFields(communities.fr.fields, "(Voisinage)"),
      deleted_at: "supprimé",
      image: "photo du véhicule",
      instructions: "instructions",
      location_description: "précisions sur l'emplacement",
      name: "nom",
      owner: owners.fr.fields,
      owner_id: "propriétaire",
      padlock_id: "cadenas",
      position: "position géographique",
      share_with_parent_communities:
        "j'accepte que mon véhicule soit partagé au quartier d'appartenance",
      share_with_parent_communities_dynamic:
        "j'accepte que mon véhicule soit partagé avec {shared_with}",
      type: "type de véhicule",
      types: {
        bike: "vélo",
        car: "auto",
        null: "n'importe quel type",
        trailer: "remorque",
      },
      is_deleted: "afficher les véhicules archivés",
      is_self_service: "Véhicule en libre service",
    },
    model: {
      singular: "véhicule",
    },
    model_name: "véhicule | véhicules",
    papers_locations: {
      in_the_car: "dans la voiture",
      to_request_with_car: "à récupérer avec la voiture",
    },
    placeholders: {
      comments:
        "ex.: La voiture est généralement garée dans la ruelle.\n" +
        "SVP ne pas fumer dans mon auto. Merci beaucoup!\n" +
        "J'ai un siège pour bébé et un support pour les vélos en arrière.",
      instructions:
        "ex.: J'apprécierais que le siège de bébé soit replacé, s'il y a lieu.\n" +
        "Le code du cadenas pour accéder à la cour est le 1234.",
      location_description: "ex.: Généralement dans la ruelle, textez-moi pour plus de précisions.",
      name: "ex.: la LocoMobile bleue",
    },
    sizes: {
      big: "grand",
      medium: "moyen",
      small: "petit",
      kid: "enfant",
    },
    transmission_modes: cars.fr.transmission_modes,
    véhicule: "véhicule | véhicules",
  },
};
