import RestModule from '../RestModule';

export default new RestModule('invoices', {
  params: {
    order: '-created_at',
    page: 1,
    per_page: 10,
  },
  exportFields: [
    'id',
    'items_count',
    'total',
    'total_tps',
    'total_tvq',
    'total_with_taxes',
    'period',
    'paid_at',
    'created_at',
    'updated_at',
    'payment_method.id',
    'payment_method.name',
    'user.id',
    'user.name',
    'user.last_name',
    'bill_items.id',
    'bill_items.label',
    'bill_items.amount',
    'bill_items.item_date',
    'bill_items.taxes_tps',
    'bill_items.taxes_tvq',
  ],
});
