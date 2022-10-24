import prefixFields from "@/locales/prefixFields";

import borrowers from "./borrowers";
import communities from "./communities";
import loanables from "./loanables";

const statuss = {
  null: "-",
  in_process: "En cours",
  canceled: "Annulé",
  completed: "Complété",
};

export default {
  fr: {
    "créer un emprunt": "créer un emprunt",
    descriptions: {
      platform_tip:
        "LocoMotion est un projet citoyen et collaboratif. Participez à son " +
        "fonctionnement en donnant une contribution volontaire.",
    },
    loan_approved: "La réservation est confirmée!",
    modification_warning: {
      title: "Pour modifier votre réservation",
      content: "Merci de l'annuler et d'en créer une nouvelle.",
    },
    insurance_warning: {
      title: "Avant d'emprunter une auto",
      warning: "Lisez attentivement les {link}. {emphasis}",
      terms: "conditions d'utilisation de l'assurance",
      terms_link: "/conditions-utilisation-desjardins-assurances.html",
      car_stays_in_quebec: "Notez que l'assurance couvre seulement les trajets au Québec.",
    },
    fields: {
      id: "ID",
      actual_duration_in_minutes: "durée (minutes)",
      borrower_id: "emprunteur-se",
      borrower: borrowers.fr.fields,
      calendar_days: "jours calendaires",
      community_id: "communauté",
      community: {
        ...prefixFields(communities.fr.fields, "(Communauté)"),
        model_name: communities.fr.model_name,
      },
      current_step: "étape en cours",
      departure_at: "départ",
      duration_in_minutes: "durée (minutes)",
      estimated_distance: "distance prévue (km)",
      estimated_insurance: "coût estimé de l'assurance",
      estimated_price: "coût estimé",
      incidents: {
        status: "statut (Incident)",
        statuss, // FIXME
      },
      loanable_id: loanables.fr.model.singular,
      loanable: {
        ...prefixFields(loanables.fr.fields, "(Véhicule)"),
        model_name: loanables.fr.model_name,
      },
      status: "statut de l'emprunt",
      statuss: statuss,
      loanable_type: "type de véhicule",
      message_for_owner: "message pour le propriétaire",
      platform_tip: "contribution volontaire LocoMotion",
      price: "prix",
      reason: "raison de l'utilisation",
      return_at: "retour",
      takeover: {
        status: "statut (Prise de possession)",
        statuss, // FIXME
      },
    },
    model: {
      singular: "emprunt",
    },
    model_name: "emprunt | emprunts",
    placeholders: {
      reason: "ex.: épicerie, déménagement, etc.",
    },
    payment: {
      time_distance: "temps prévu et distance",
      deductions: "dépenses déduites",
      transferred_amount: "montant transféré entre utilisateurs",
      owner_total: "montant reçu",
      borrower_total: "total pour l'emprunteur-se",
      insurance: "assurances",
      tip: "contribution volontaire",
      total: "total",
      tarification_explanation: "explication de la tarification",
    },
    details_box: {
      details: "détails",
      duration: "durée",
      duration_over_days: "{hours} | {hours} | {hours} sur {days} jours",
      estimated_distance: "distance prévue",
      distance: "distance",
      cost: "coût",
      tip_modifiable: "Pourra être modifiée lors du paiement final.",
    },
    statuses: {
      self_service: "En libre service",
      new: {
        text: "Nouvel emprunt",
        description: "Emprunt en cours de création!",
      },
      canceled: {
        text: "Emprunt annulé",
        description: "L'emprunt n'a pas pu être complété.",
      },
      active_incident: {
        text: "Incident",
        description: "Un incident est en cours de résolution.",
      },
      contested: {
        text: "Informations contestées",
        description: "Les informations au retour ou au départ ont été contestées.",
      },
      completed: {
        text: "Emprunt complété",
        description: "L'emprunt a été complété avec succès!",
      },
      waiting_for_approval: {
        text: "Attente d'approbation",
        description: "Le propriétaire du véhicule doit approuver la demande d'emprunt.",
      },
      waiting_for_prepayment: {
        text: "Attente de prépaiement",
        description:
          "L'emprunteur-se doit ajouter des fonds à son solde avant de débuter l'emprunt.",
      },
      expired_reservation: {
        text: "Réservation expirée",
        description:
          "La réservation du véhicule est terminée. Vous pouvez clore l'emprunt en ligne.",
      },
      waiting_for_takeover_self_service: {
        text: "Réservation en cours",
        description: "La réservation du véhicule est en cours! Démarrez l'emprunt en ligne.",
      },
      confirmed_reservation_self_service: {
        text: "Réservation confirmée",
        description: "La réservation est confirmée!",
      },
      waiting_for_takeover: {
        text: "Attente du début de l'emprunt",
        description: "La réservation du véhicule est en cours! Démarrez l'emprunt en ligne.",
      },
      confirmed_reservation: {
        text: "Emprunt confirmé",
        description:
          "L'emprunt est confirmé! Remplissez l'information en ligne lorsque l'emprunteur-se prend possession du véhicule.",
      },
      waiting_for_extension: {
        text: "Attente d'approbation de retard",
        description: "Le-a propriétaire doit approuver la demande de retard du véhicule.",
      },
      ongoing_reservation_self_service: {
        text: "Réservation en cours",
        description: "La réservation du véhicule est en cours!",
      },
      ongoing_reservation: {
        text: "Emprunt en cours",
        description: "L'emprunt du véhicule est en cours!",
      },
      waiting_for_handover: {
        text: "Réservation terminée",
        description:
          "La réservation du véhicule est terminée. Veuillez le retourner et compléter les étapes l'emprunt en ligne.",
      },
      waiting_for_payment_self_service: {
        text: "Attente de la fin de l'emprunt",
        description:
          "Merci pour votre emprunt! Vous pouvez maintenant clore l'emprunt en ligne et offrir une contribution volontaire.",
      },
      waiting_for_payment: {
        text: "Attente de paiement",
        description: "L'emprunteur-se doit payer l'emprunt en ligne.",
      },
    },
  },
};
