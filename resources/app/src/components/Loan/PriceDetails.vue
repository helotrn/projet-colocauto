<template>
  <div>
    <dt class="mb-2 details-toggle" v-b-toggle.price-details>
      {{
        loan.final_price
          ? $t("details_box.cost")
          : $t("fields.estimated_price") | capitalize
      }}
      &nbsp;
      <b-icon icon="caret-right-fill"></b-icon>
    </dt>
    <dd>
      <div v-if="showCostSummary" class="font-weight-bold">
        <layout-loading with-text v-if="loanLoading"></layout-loading>
        <template v-else>
          <div v-if="isLoanAdmin">
            {{ $t("payment.transferred_amount") | capitalize }}: {{ ownerTotal | currency
            }}<br />
            {{ $t("payment.borrower_total") | capitalize }}: {{ borrowerTotal | currency }}
          </div>
          <div v-else-if="isBorrower">{{ borrowerTotal | currency }}</div>
          <div v-else-if="isOwner">{{ ownerTotal | currency }}</div>
        </template>
      </div>
      <b-collapse
        @input="(visible) => (showCostSummary = !visible)"
        :visible="isLoanAdmin"
        id="price-details"
        role="tabpanel"
        accordion="price-details"
      >
        <layout-loading v-if="loanLoading" class="table-loading-indicator"></layout-loading>
        <table class="price-table" :class="{ loading: loanLoading }">
          <tr>
            <th>{{ $t("payment.time_distance") | capitalize }}</th>
            <td class="text-right tabular-nums">{{ price | currency }}</td>
          </tr>
          <tr>
            <th :class="{ 'pb-2': !isBorrower }">
              {{ $t("payment.deductions") | capitalize }}
            </th>
            <td class="text-right tabular-nums">
              {{ -purchasesAmount | currency }}
            </td>
          </tr>
          <tr v-if="!isBorrower" class="total-row">
            <th v-if="isLoanAdmin">
              {{ $t("payment.transferred_amount") | capitalize }}
            </th>
            <th v-else-if="isOwner">{{ $t("payment.owner_total") | capitalize }}</th>
            <td
              v-if="isOwner || isLoanAdmin"
              class="text-right tabular-nums border-top border-dark"
            >
              {{ ownerTotal | currency }}
            </td>
          </tr>
          <template v-if="isBorrower || isLoanAdmin">
            <tr class="total-row">
              <th>
                {{
                  isLoanAdmin
                    ? $t("payment.borrower_total")
                    : $t("payment.total") | capitalize
                }}
              </th>
              <td class="text-right tabular-nums border-top border-dark">
                {{ borrowerTotal | currency }}
              </td>
            </tr>
          </template>
        </table>
        <div class="small">
          <a href="https://www.colocauto.org/tarification" target="_blank">{{
            $t("payment.tarification_explanation") | capitalize
          }}</a>
        </div>
      </b-collapse>
    </dd>
  </div>
</template>


<script>
import locales from "@/locales";
import UserMixin from "@/mixins/UserMixin";

export default {
  name: "LoanPriceDetails",
  mixins: [UserMixin],
  props: {
    loan: {
      type: Object,
      required: false,
    },
    loanLoading: {
      type: Boolean,
      required: true,
      default: false,
    },
  },
  data: function () {
    return { showCostSummary: !this.isLoanAdmin };
  },
  computed: {
    isLoanAdmin() {
      return this.loan.loanable && this.isAdminOfLoanable(this.loan.loanable);
    },
    isBorrower() {
      return this.user.id === this.loan.borrower.user.id;
    },
    isOwner() {
      return this.user.id === this.loan.loanable?.owner?.user.id;
    },
    price() {
      return this.loan.final_price
        ? parseFloat(this.loan.final_price)
        : this.loan.actual_price
        ? parseFloat(this.loan.actual_price)
        : parseFloat(this.loan.estimated_price);
    },
    purchasesAmount() {
      return this.loan.handover?.purchases_amount
        ? parseFloat(this.loan.handover.purchases_amount)
        : 0;
    },
    ownerTotal() {
      return this.price - this.purchasesAmount;
    },
    insurance() {
      return this.loan.final_insurance
        ? parseFloat(this.loan.final_insurance)
        : this.loan.actual_insurance
        ? parseFloat(this.loan.actual_insurance)
        : parseFloat(this.loan.estimated_insurance);
    },
    tip() {
      const tip = this.loan.final_platform_tip
        ? parseFloat(this.loan.final_platform_tip)
        : parseFloat(this.loan.platform_tip);
      return isNaN(tip) ? 0 : Math.max(tip, 0);
    },
    borrowerTotal() {
      return this.price - this.purchasesAmount + this.insurance + this.tip;
    },
  },
  i18n: {
    messages: {
      en: {
        loanable: locales.en.loanables,
        ...locales.en.loans,
      },
      fr: {
        loanable: locales.fr.loanables,
        ...locales.fr.loans,
      },
    },
  },
}
</script>

<style lang="scss">
  .details-toggle {
    &:hover {
      text-decoration: underline;
    }
    .b-icon {
      transition: 0.3s;
    }
    &.not-collapsed .b-icon {
      transform: rotate(90deg);
    }
  }
  .table-loading-indicator {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    height: 75%;
    opacity: 0.5;
  }
  .price-table {
    width: 100%;
    font-size: 0.8rem;

    &.loading {
      opacity: 0.5;
    }

    th {
      font-weight: 400;
    }
    td {
      vertical-align: top;
    }
    .total-row {
      & > td,
      th {
        font-weight: bold;
        padding-top: 0.5rem;
      }
    }
  }
</style>
