import RestModule from './RestModule';

export default new RestModule('loans', {
  params: {
    order: '-created_at',
    page: 1,
    per_page: 10,
  },
});
