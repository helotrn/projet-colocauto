import users from "./users";

export default {
  fr: {
    fields: {
      user: Object.keys(users.fr.fields).reduce((acc, f) => {
        acc[f] = `${users.fr.fields[f]} (Propriétaire)`;
        return acc;
      }, {}),
    },
  },
};
