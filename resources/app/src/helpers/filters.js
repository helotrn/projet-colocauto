import dayjs from "./dayjs";

const capitalize = (value) => {
  if (!value) {
    return "";
  }
  const string = value.toString();
  return string.charAt(0).toUpperCase() + string.slice(1);
};

const currency = (value) => {
  const floatVal = parseFloat(value);

  if (Number.isNaN(floatVal)) {
    return "";
  }

  return `${floatVal.toFixed(2).replace(".", ",")}$`;
};

const percent = (value) => {
  const floatVal = parseFloat(value);

  if (Number.isNaN(floatVal)) {
    return "";
  }

  return `${(floatVal * 100).toFixed(2)}%`;
};

const datetime = (value) => {
  if (!value) {
    return "";
  }

  return dayjs(value).format("D MMMM YYYY HH:mm:ss");
};

const date = (value) => {
  if (!value) {
    return "";
  }

  return dayjs(value).format("D MMMM YYYY");
};

const shortDate = (value) => {
  if (!value) {
    return "";
  }

  const date = dayjs(value);
  if (date.year === dayjs().year) {
    return date.format("ddd. D MMM.");
  }

  return date.format("ddd. D MMM. YYYY");
};

const durationInHours = (valueInMinutes) => {
  if (valueInMinutes < 60) {
    return `${valueInMinutes} minutes`;
  }
  let hours = Math.floor(valueInMinutes / 60);
  let minutes = valueInMinutes - hours * 60;
  return `${hours}h ${minutes}m`;
};

const day = (value) => {
  if (!value) {
    return "";
  }

  return dayjs(value).format("dddd");
};

const phone = (value) => {
  const m = value
    .toString()
    .match(/^\(?([1-9][0-9]{2})([- ]*|\) ?)?([1-9][0-9]{2})[- ]?([0-9]{4})$/);

  if (m) {
    return `(${m[1]}) ${m[3]}-${m[4]}`;
  }

  return "";
};

const time = (value) => {
  if (!value) {
    return "";
  }

  return dayjs(value).format("HH:mm");
};

const titleize = (value) => {
  if (!value) {
    return "";
  }

  const parts = value.toString().split(/ /);

  return parts.map(capitalize).join(" ");
};

export {
  capitalize,
  currency,
  date,
  datetime,
  day,
  durationInHours,
  percent,
  phone,
  time,
  titleize,
  shortDate,
};
