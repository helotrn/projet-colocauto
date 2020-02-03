export default function extractErrors(errResponse) {
  if (!errResponse || !errResponse.errors) {
    return [];
  }

  const { errors } = errResponse;

  return Object.keys(errResponse.errors).reduce((acc, k) => {
    acc.push(...errors[k]);
    return acc;
  }, []);
}
