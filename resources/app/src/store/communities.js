import RestModule from './RestModule';

export default new RestModule('communities', {
  params: {
    page: 1,
    per_page: 10,
    q: '',
    order: 'name',
  },
});
