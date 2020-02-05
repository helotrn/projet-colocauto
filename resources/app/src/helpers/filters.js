const capitalize = (value) => {
  if (!value) {
    return '';
  }
  const string = value.toString();
  return string.charAt(0).toUpperCase() + string.slice(1);
};
export default {
  capitalize,
  titleize(value) {
    if (!value) {
      return '';
    }

    const parts = value.toString().split(/ /);

    return parts.map(capitalize).join(' ');
  },
};
