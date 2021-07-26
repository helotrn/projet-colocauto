export default function distance(from, to) {
  return Math.abs(Math.sqrt((to.lat - from.lat) ** 2 + (to.lng - from.lng) ** 2));
}
