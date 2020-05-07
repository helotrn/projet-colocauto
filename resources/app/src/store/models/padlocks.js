import RestModule from '../RestModule';

export default new RestModule('padlocks', {
  params: {
    order: 'name',
    page: 1,
    per_page: 10,
    q: '',
    type: null,
    deleted_at: null,
  },
  exportFields: [
    'id',
    'external_id',
    'name',
    'mac_addresss',
    'loanable.id',
    'loanable.name',
  ],
});
