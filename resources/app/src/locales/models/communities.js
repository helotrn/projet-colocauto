import { prefixFields } from "@/helpers";

import tags from "./tags";

const i18n = {
  fr: {
    communauté: "communauté | communautés",
    "créer une communauté": "créer une communauté",
    "{count} communauté sélectionnée":
      "aucune communauté sélectionnée | {count} communauté sélectionnée | {count} communautés sélectionnées",
    fields: {
      chat_group_url: "URL du groupe de discussion",
      // When community is referred to from outisde of this context.
      community: {
        id: "ID communauté",
        name: "Communauté",
      },
      description: "description",
      id: "ID",
      long_description: "texte de bienvenue / présentation",
      name: "nom",
      proof: "preuve de résidence",
      tags: {
        ...prefixFields(tags.fr.fields, "(Mot-clé)"),
        model_name: tags.fr.model_name,
      },
      type: "type",
      types: {
        neighborhood: "voisinage",
        null: "n'importe quel type",
        borough: "quartier",
        private: "privée",
      },
      user: {
        id: "ID utilisateur-rice",
        name: "Utilisateur-rice",
        role: "Rôle",
        role_labels: {
          member: "Membre",
          admin: "Admin",
        },
        approved_at: "Approuvé-e",
        suspended_at: "Suspendu-e",
        proof: "Preuve",
        actions: "Actions",
      },
    },
    model_name: "communauté | communautés",
    user_proof_of_residence: "Preuve de résidence ({user_full_name})",
  },
  en: {
    communauté: "community | communities",
    "créer une communauté": "create a community",
    "{count} communauté sélectionnée":
      "no community selected | {count} community selected | {count} communities selected",
    fields: {
      // When community is referred to from outisde of this context.
      community: {
        id: "Community ID",
        _name: "Community",
      },
      description: "description",
      id: "ID",
      name: "name",
      tags: {
        ...tags.fr.fields,
        model_name: tags.fr.model_name,
      },
      type: "type",
      types: {
        neighborhood: "neighborhood",
        null: "any type",
        borough: "borough",
        private: "private",
      },
      user: {
        id: "User ID",
        name: "User",
        role: "Role",
        role_labels: {
          member: "Member",
          admin: "Admin",
        },
        approved_at: "Approved",
        suspended_at: "Suspended",
        proof: "Proof",
        actions: "Actions",
      },
    },
    user_proof_of_residence: "Proof of residence ({user_full_name})",
  },
};

i18n.fr.fields.parent_id = "quartier";
i18n.fr.fields.parent = {
  ...prefixFields(i18n.fr.fields, "(Quartier)"),
  model_name: i18n.fr.model_name,
};

export default i18n;
