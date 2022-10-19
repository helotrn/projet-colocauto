const prefixFields = (fields, prefix) =>
  Object.keys(fields).reduce((acc, f) => {
    if (!!fields[f] && typeof fields[f] === "object") {
      acc[f] = prefixFields(fields[f], prefix);
    } else {
      acc[f] = `${fields[f]} ${prefix}`;
    }

    return acc;
  }, {});

export default prefixFields;
