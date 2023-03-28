import prefixFields from "@/locales/prefixFields";

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
      accept_conditions: `j'accepte les <a href="https://www.colocauto.org/conditions" target="_blank">conditions
        générales d'utilisation</a>`,
      gdpr: `Mobicoop traite les données recueillies pour assurer le bon fonctionnement
        du service Coloc'Auto. Pour en savoir plus sur la gestion de vos données personnelles
        et pour exercer vos droits, reportez-vous à notre <a href="https://www.colocauto.org/confidentialite"
        target="_blank">politique de confidentialité</a>`,
      newsletter: "J'accepte de recevoir des courriels de la part de l'équipe Coloc'Auto concernant le projet",
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
      is_deleted: "afficher les membres archivés",
    },
    list: {
      create: "ajouter un membre",
      selected: "{count} membre sélectionné | {count} membres sélectionnés",
    },
    password_change: {
      title: "Mot de passe mis à jour",
      content: `<p>Le mot de passe de l'utilisateur a été mis à jour.</p>`,
    },
    archive: "Archiver",
    approve: "Approuver",
    mandate_tool_tip: "Cliquez ici pour vous connecter à la place de l'utilisateur",
    model_name: "membre | membres",
    payment_methods: paymentMethods.fr,
    restore: "Restaurer",
    suspend: "Suspendre",
  },
};
