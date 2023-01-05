import Wallet from "@/views/Wallet.vue";
import WalletExpenses from "@/views/wallet/Expenses.vue";
import WalletExpense from "@/views/wallet/Expense.vue";

export default [
  {
    path: "/wallet",
    name: "wallet",
    component: Wallet,
    meta: {
      auth: true,
      title: "titles.wallet",
    },
    children: [
      {
        path: "expenses",
        component: WalletExpenses,
        meta: {
          auth: true,
          creatable: true,
          title: "titles.expenses",
          slug: "expenses",
          data: {
            expenses: {
              retrieve: {
                fields: "id,name,amount,executed_at,user.full_name,loanable.name,tag.name,tag.color",
                "user.id": "me",
              },
            },
          },
        },
      },
      {
        path: "expenses/:id",
        component: WalletExpense,
        props: true,
        meta: {
          auth: true,
          slug: "expenses",
          params: {
            fields: "id,name,amount,executed_at,user_id,loanable_id,expense_tag_id"
          },
          title: "titles.expense",
        },
      },
    ],
  },
];
