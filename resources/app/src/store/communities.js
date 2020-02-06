import RestModule from './RestModule';

export default new RestModule('communities', {
  params: {
    order: 'name',
    page: 1,
    per_page: 10,
    q: '',
    type: null,
  },
});
