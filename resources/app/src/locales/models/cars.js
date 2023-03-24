export default {
  fr: {
    descriptions: {
      report: `Téléchargez et remplissez la fiche puis gardez-en une copie imprimée dans le carnet de bord et une copie virtuelle ici.
        Lorsque vous partagerez votre auto, vous pourrez vous appuyer sur cette fiche dans vos discussions. Gardez-la bien à jour !`,
    },
    engines: {
      fuel: "essence",
      diesel: "diesel",
      electric: "électrique",
      hybrid: "hybride",
    },
    fields: {
      brand: "marque",
      engine: "moteur",
      has_informed_insurer: "l'assureur a été informé",
      insurer: "assureur",
      is_value_over_fifty_thousand:
        "cochez la case si la valeur à neuf de ce véhicule dépasse 50 000$",
      model: "modèle",
      papers_location: "emplacement des papiers",
      plate_number: "numéro de plaque",
      pricing_category: "catégorie de tarification",
      report: "fiche état du véhicule",
      report_download: "télécharger le gabarit de la fiche",
      transmission_mode: "transmission",
      year_of_circulation: "année de mise en circulation",
      cost_per_km: "coût en € par kilomètre",
      cost_per_month: "provisions",
      owner_compensation: "dédommagement propriétaire",
    },
    descriptions: {
      cost_per_km: 
        "Ce coût s’applique aux emprunts réalisées avec ce véhicule, et viendra" +
        " automatiquement s’ajouter à la liste des dépenses du groupe.",
      cost_per_month:
        "Les provisions sont partagées mensuellement entre les membres du groupe." +
        " Elles couvrent des dépenses telles que l'assurance, l'entretien et la" +
        " décote du véhicule.",
      owner_compensation:
        "Ce montant est partagé mensuellement entre les membres du groupe (à" +
        " l'exception du. de la propriétaire). Il permet de dédommager le ou la" +
        " propriétaire du véhicule.",
    },
    transmission_modes: {
      automatic: "automatique",
      manual: "manuelle",
    },
  },
};
