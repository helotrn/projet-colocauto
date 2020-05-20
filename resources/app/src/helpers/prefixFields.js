import { capitalize } from './filters';

const modelNameOrKey = (key, fields) => {
  if (fields[key].model_name) {
    return capitalize(fields[key].model_name.split(' | ')[0]);
  }

  return capitalize(key);
}

const prefixFields = (fields, prefix) => {
  return Object.keys(fields).reduce((acc, f) => {
    if (!!fields[f] && typeof fields[f] === 'object') {
      acc[f] = prefixFields(fields[f], prefix);
    } else {
      acc[f] = `${fields[f]} ${prefix}`;
    }

    return acc;
  }, {});
};

export default prefixFields;
