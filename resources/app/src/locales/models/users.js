import { prefixFields } from "@/helpers";

import communities from "./communities";
import paymentMethods from "./paymentMethods";

export default {
  fr: {
    descriptions: {
      description:
        "Partagez ce qu’il vous plait! Vos passions, le nom de vos enfants, votre film préféré ou vos plus grandes folies!",
      avatar:
        "Ajoutez une photo de profil. On vous encourage à vous montrer la binette! " +
        "Ça aide à se faire confiance et à mieux profiter des avantages du partage.",
    },
    fields: {
      accept_conditions: `j'accepte les <a href="/conditions" target="_blank">conditions
        générales d'utilisation</a> incluant des communications par courriel`,
      address: "adresse",
      avatar: "image de profil",
      communities: prefixFields(communities.fr.fields, "(Communauté)"),
      created_at: "créé",
      date_of_birth: "date de naissance",
      deleted_at: "supprimé",
      description: "description",
      email: "courriel",
      full_name: "nom complet",
      id: "ID",
      is_smart_phone: "téléphone intelligent?",
      last_name: "nom",
      name: "prénom",
      other_phone: "autre numéro de téléphone",
      phone: "téléphone",
      postal_code: "code postal",
      is_deactivated: "afficher les membres désactivés",
    },
    list: {
      create: "ajouter un membre",
      selected: "{count} membre sélectionné | {count} membres sélectionnés",
    },
    model_name: "membre | membres",
    payment_methods: paymentMethods.fr,
    mandate_tool_tip: "Cliquez ici pour vous connecter à la place de l'utilisateur",
  },
};
