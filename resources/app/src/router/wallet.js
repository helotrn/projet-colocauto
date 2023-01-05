import Wallet from "@/views/Wallet.vue";
import WalletExpenses from "@/views/wallet/Expenses.vue";

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
                fields: "id,name,amount,executed_at,user.full_name,loanable.name",
                "user.id": "me",
              },
            },
          },
        },
      },
    ],
  },
];
