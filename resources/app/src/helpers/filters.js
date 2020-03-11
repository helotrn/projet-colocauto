import dayjs from './dayjs';

const capitalize = (value) => {
  if (!value) {
    return '';
  }
  const string = value.toString();
  return string.charAt(0).toUpperCase() + string.slice(1);
};

const titleize = (value) => {
  if (!value) {
    return '';
  }

  const parts = value.toString().split(/ /);

  return parts.map(capitalize).join(' ');
};

const datetime = (value) => {
  if (!value) {
    return '';
  }

  return dayjs(value).format('D MMMM YYYY HH:mm:ss');
};

const date = (value) => {
  if (!value) {
    return '';
  }

  return dayjs(value).format('D MMMM YYYY');
};

const currency = (value) => {
  const floatVal = parseFloat(value, 10);

  if (Number.isNaN(floatVal)) {
    return '';
  }

  return `${floatVal.toFixed(2)}$`
};

export {
  capitalize,
  currency,
  date,
  datetime,
  titleize,
};
