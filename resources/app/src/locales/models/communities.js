import { prefixFields } from '@/helpers';

import tags from './tags';

const i18n = {
  fr: {
    communauté: 'communauté | communautés',
    'créer une communauté': 'créer une communauté',
    '{count} communauté sélectionnée': 'aucune communauté sélectionnée | {count} communauté sélectionnée | {count} communautés sélectionnées',
    fields: {
      description: 'description',
      id: 'ID',
      long_description: 'texte de bienvenue / présentation',
      name: 'nom',
      proof: 'preuve de résidence',
      tags: {
        ...prefixFields(tags.fr.fields, '(Mot-clé)'),
        model_name: tags.fr.model_name,
      },
      type: 'type',
    },
    model_name: 'communauté | communautés',
    types: {
      neighborhood: 'voisinage',
      null: "n'importe quel type",
      borough: 'quartier',
      private: 'privée',
    },
  },
  en: {
    communauté: 'community | communities',
    'créer une communauté': 'create a community',
    '{count} communauté sélectionnée': 'no community selected | {count} community selected | {count} communities selected',
    fields: {
      description: 'description',
      id: 'ID',
      name: 'name',
      type: 'type',
      tags: {
        ...tags.fr.fields,
        model_name: tags.fr.model_name,
      },
    },
    types: {
      neighborhood: 'neighborhood',
      null: 'any type',
      borough: 'borough',
      private: 'private',
    },
  },
};

i18n.fr.fields.parent_id = 'quartier';
i18n.fr.fields.parent = {
  ...prefixFields(i18n.fr.fields, '(Quartier)'),
  model_name: i18n.fr.model_name,
};

export default i18n;
