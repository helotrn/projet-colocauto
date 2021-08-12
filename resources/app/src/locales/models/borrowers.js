import users from "./users";

export default {
  fr: {
    fields: {
      drivers_license_number: "numéro de permis de conduire",
      gaa: "rapport de sinistre GAA",
      has_been_sued_last_ten_years: "a été poursuivi dans les 10 dernières années",
      noke_id: "ID Noke",
      saaq: "dossier de conduite de la SAAQ",
      user: Object.keys(users.fr.fields).reduce((acc, f) => {
        acc[f] = `${users.fr.fields[f]} (Emprunteur)`;
        return acc;
      }, {}),
    },
    placeholders: {
      drivers_license_number: "ex.: L1234-456789-09",
    },
  },
};
