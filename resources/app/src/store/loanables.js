import RestModule from './RestModule';

export default new RestModule('loanables', {
  params: {
    order: 'name',
    page: 1,
    per_page: 2,
    q: '',
    type: null,
  },
});
