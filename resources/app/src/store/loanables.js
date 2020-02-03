import RestModule from './RestModule';

export default new RestModule('loanables', {
  params: {
    page: 1,
    per_page: 10,
    q: '',
    order: 'name',
    type: null,
  },
});
