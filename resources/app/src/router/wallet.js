import Wallet from "@/views/Wallet.vue";
import WalletExpenses from "@/views/wallet/Expenses.vue";
import WalletExpense from "@/views/wallet/Expense.vue";
import WalletRefunds from "@/views/wallet/Refunds.vue";
import WalletRefund from "@/views/wallet/Refund.vue";
import WalletBalance from "@/views/wallet/Balance.vue";

export default [
  {
    path: "/wallet",
    name: "wallet",
    component: Wallet,
    meta: {
      auth: true,
      title: "wallet.titles.wallet",
    },
    children: [
      {
        path: "expenses",
        component: WalletExpenses,
        meta: {
          auth: true,
          creatable: true,
          title: "wallet.titles.expenses",
          slug: "expenses",
          data: {
            expenses: {
              retrieve: {
                fields: "id,name,amount,executed_at,user.full_name,loanable.name,tag.name,tag.color,changes.id,locked",
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
            fields: "id,name,amount,executed_at,user_id,loanable_id,expense_tag_id,changes,changes.user,changes.description,changes.created_at,locked"
          },
          title: "wallet.titles.expense",
        },
      },
      {
        path: "refunds",
        component: WalletRefunds,
        meta: {
          auth: true,
          creatable: true,
          title: "wallet.titles.refunds",
          slug: "refunds",
          data: {
            refunds: {
              retrieve: {
                fields: "id,amount,executed_at,user.full_name,credited_user.full_name"
              },
            },
          },
        },
      },
      {
        path: "refunds/:id",
        component: WalletRefund,
        props: true,
        meta: {
          auth: true,
          slug: "refunds",
          params: {
            fields: "id,amount,executed_at,user_id,credited_user_id"
          },
          title: "wallet.titles.refunds",
        },
      },
      {
        path: "balance",
        component: WalletBalance,
        meta: {
          auth: true,
          title: "wallet.titles.balance",
          slug: "balance",
        },
      },
    ],
  },
];
