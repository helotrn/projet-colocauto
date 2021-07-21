import loanables from "./loanables";

export default {
  fr: {
    cadenas: "cadenas",
    fields: {
      deleted_at: "supprimé",
      name: "nom",
      mac_address: "adresse MAC",
      external_id: "ID externe",
      loanable_id: loanables.fr.model.singular,
      loanable: Object.keys(loanables.fr.fields).reduce((acc, f) => {
        acc[f] = `${loanables.fr.fields[f]} (Objet)`;
        return acc;
      }, {}),
    },
  },
};
