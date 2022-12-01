<template>
  <div class="loanable-details-box">
    <b-card :img-src="loanable.image ? loanable.image.url : ''" img-top>
      <b-card-title class="mb-2">
        <a v-if="loanableUrl" :href="loanableUrl">{{ loanable.name }}</a>
        <span v-else>{{ loanable.name }}</span>
      </b-card-title>
      <div class="details-toggle mb-3 text-muted" v-b-toggle.loanable-details>
        {{ $t("details_box.details") | capitalize }} {{ prettyType }}
        <b-icon icon="caret-right-fill"></b-icon>
      </div>
      <b-card-text>
        <b-collapse id="loanable-details" role="tabpanel" accordion="loanable-details">
          <div>
            <div v-if="loanable.type === 'bike'">
              <dl>
                <dt>{{ $t("loanable.fields.model") | capitalize }}</dt>
                <dd>{{ loan.bike.model }}</dd>
                <dt>{{ $t("loanable.fields.bike_type") | capitalize }}</dt>
                <dd>{{ $t(`loanable.bike_types.${loan.bike.bike_type}`) | capitalize }}</dd>
                <dt>{{ $t("loanable.fields.size") | capitalize }}</dt>
                <dd>
                  {{ $t(`loanable.sizes.${loan.bike.size}`) | capitalize }}
                </dd>
              </dl>
            </div>
            <div v-else-if="loanable.type === 'trailer'">
              <dl>
                <dt>{{ $t("loanable.fields.maximum_charge") | capitalize }}</dt>
                <dd>{{ loan.trailer.maximum_charge }}</dd>
                <dt>{{ $t("loanable.fields.dimensions") | capitalize }}</dt>
                <dd>{{ loan.trailer.dimensions }}</dd>
              </dl>
            </div>
            <div v-else-if="loanable.type === 'car'">
              <dl>
                <dt>{{ $t("loanable.fields.brand") | capitalize }}</dt>
                <dd>{{ loan.car.brand }}</dd>
                <dt>{{ $t("loanable.fields.model") | capitalize }}</dt>
                <dd>{{ loan.car.model }}</dd>
                <dt>{{ $t("loanable.fields.year_of_circulation") | capitalize }}</dt>
                <dd>{{ loan.car.year_of_circulation }}</dd>
                <dt>{{ $t("loanable.fields.transmission_mode") | capitalize }}</dt>
                <dd>
                  {{ $t(`loanable.transmission_modes.${loan.car.transmission_mode}`) | capitalize }}
                </dd>
                <dt>{{ $t("loanable.fields.engine") | capitalize }}</dt>
                <dd>{{ $t(`loanable.engines.${loan.car.engine}`) | capitalize }}</dd>
                <dt>{{ $t("loanable.fields.papers_location") | capitalize }}</dt>
                <dd>
                  {{ $t(`loanable.papers_locations.${loan.car.papers_location}`) | capitalize }}
                </dd>
              </dl>
            </div>
          </div>
        </b-collapse>
        <hr />
        <template v-if="!isOwner || ownerUrl">
          <dl>
            <dt>{{ $t("loanable.fields.owner_id") | capitalize }}</dt>
            <dd class="owner-details">
              <a :href="ownerUrl" v-if="ownerUrl">
                {{ loanable.owner.user.full_name }}
              </a>
              <span v-else>{{ loanable.owner.user.full_name }}</span>
              <br v-if="loanable.owner.user.email" />
              {{ loanable.owner.user.email }}
              <br v-if="loanable.owner.user.phone" />
              {{ loanable.owner.user.phone }}
            </dd>
          </dl>
        </template>

        <template v-if="!isBorrower || borrowerUrl">
          <dl>
            <dt>{{ $t("fields.borrower_id") | capitalize }}</dt>
            <dd>
              <a :href="borrowerUrl" v-if="borrowerUrl">
                {{ loan.borrower.user.full_name }}
              </a>
              <span v-else>{{ loan.borrower.user.full_name }}</span>
              <br v-if="loan.borrower.user.email" />
              {{ loan.borrower.user.email }}
              <br v-if="loan.borrower.user.phone" />
              {{ loan.borrower.user.phone }}
            </dd>
          </dl>
        </template>

        <hr />
        <b-row>
          <b-col tag="dl" cols="6">
            <dt>{{ $t("fields.departure_at") | capitalize }}</dt>
            <dd>
              {{ loan.departure_at | shortDate | capitalize }}<br />{{ loan.departure_at | time }}
            </dd>
          </b-col>
          <b-col tag="dl" cols="6">
            <dt>{{ $t("fields.return_at") | capitalize }}</dt>
            <dd>
              {{ returnAt | shortDate | capitalize }}<br />
              {{ returnAt | time }}
            </dd>
          </b-col>
        </b-row>
        <b-row>
          <b-col tag="dl" cols="6">
            <dt>{{ $t("details_box.duration") | capitalize }}</dt>
            <dd v-if="duration > 0">
              {{
                $tc("details_box.duration_over_days", loan.calendar_days, {
                  hours: durationInHours(duration),
                  days: loan.calendar_days,
                })
              }}
            </dd>
          </b-col>
          <b-col tag="dl" cols="6" v-if="price > 0 && distance > 0">
            <dt>
              {{
                hasFinalDistance
                  ? $t("details_box.distance")
                  : $t("details_box.estimated_distance") | capitalize
              }}
            </dt>
            <dd>{{ distance }} km</dd>
          </b-col>
          <b-col cols="12" v-if="price > 0 || insurance > 0">
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
                    <tr>
                      <th>{{ $t("payment.insurance") | capitalize }}</th>
                      <td class="text-right tabular-nums">
                        {{ insurance | currency }}
                      </td>
                    </tr>
                    <tr>
                      <th class="pb-2">
                        {{ $t("payment.tip") | capitalize }}
                        <div v-if="!loan.final_platform_tip" class="small muted">
                          {{ $t("details_box.tip_modifiable") }}
                        </div>
                      </th>
                      <td class="text-right tabular-nums pb-2">
                        {{ tip | currency }}
                      </td>
                    </tr>
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
                  <a href="https://mailchi.mp/solon-collectif/tarifs-locomotion" target="_blank">{{
                    $t("payment.tarification_explanation") | capitalize
                  }}</a>
                </div>
              </b-collapse>
            </dd>
          </b-col>
          <b-col v-else-if="tip > 0" cols="12">
            <dl>
              <dt>
                {{ $t("payment.tip") | capitalize }}
              </dt>
              <dd>
                {{ tip | currency }}
                <p v-if="!loan.final_platform_tip" class="small muted">
                  {{ $t("details_box.tip_modifiable") }}
                </p>
              </dd>
            </dl>
          </b-col>
        </b-row>
        <hr />

        <dl class="mb-0">
          <template v-if="loanable.comments">
            <dt>{{ $t("loanable.fields.comments") | capitalize }}</dt>
            <dd class="user_text">{{ loanable.comments }}</dd>
          </template>

          <template v-if="showInstructions && loanable.instructions">
            <dt>{{ $t("loanable.fields.instructions") | capitalize }}</dt>

            <dd class="user_text">{{ loanable.instructions }}</dd>
          </template>

          <dt>{{ $t("loanable.fields.position") | capitalize }}</dt>
          <dd>
            <div class="mb-2">{{ loanable.location_description }}</div>
          </dd>
        </dl>
      </b-card-text>
    </b-card>
  </div>
</template>

<script>
import { durationInHours } from "@/helpers/filters";

import locales from "@/locales";
import UserMixin from "@/mixins/UserMixin";

export default {
  name: "LoanDetailsBox",
  mixins: [UserMixin],
  props: {
    loan: {
      type: Object,
      required: false,
    },
    loanable: {
      type: Object,
      required: true,
    },
    loanLoading: {
      type: Boolean,
      required: true,
      default: false,
    },
    showInstructions: {
      type: Boolean,
      required: false,
      default: false,
    },
    vertical: {
      type: Boolean,
      required: false,
      default: false,
    },
  },
  data: function () {
    return { showCostSummary: !this.isLoanAdmin };
  },
  computed: {
    duration() {
      if (this.loan.actual_duration_in_minutes) {
        return this.loan.actual_duration_in_minutes;
      }
      return this.loan.duration_in_minutes;
    },
    hasFinalDistance() {
      return this.loan.handover && this.loan.handover.mileage_end;
    },
    distance() {
      if (this.hasFinalDistance) {
        return this.loan.handover.mileage_end - this.loan.takeover.mileage_beginning;
      }
      return this.loan.estimated_distance;
    },
    returnAt() {
      if (this.loan.actual_return_at) {
        return this.loan.actual_return_at;
      }
      return this.$dayjs(this.loan.departure_at)
        .add(this.loan.duration_in_minutes, "minute")
        .format("YYYY-MM-DD HH:mm:ss");
    },
    loanableUrl() {
      if (this.isLoanAdmin) {
        return "/admin/loanables/" + this.loan.loanable.id;
      }

      return "";
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
    ownerUrl() {
      const ownerId = this.loan.loanable?.owner.user.id;

      if (ownerId && this.isLoanAdmin) return "/admin/users/" + ownerId;

      return "";
    },
    borrowerUrl() {
      const borrowerId = this.loan.borrower?.user.id;

      if (borrowerId && this.isLoanAdmin) return "/admin/users/" + borrowerId;

      return "";
    },
    isLoanAdmin() {
      return this.loan.loanable && this.isAdminOfLoanable(this.loan.loanable);
    },
    isBorrower() {
      return this.user.id === this.loan.borrower.user.id;
    },
    isOwner() {
      return this.user.id === this.loan.loanable?.owner.user.id;
    },
    prettyType() {
      switch (this.loan.loanable.type) {
        case "car":
          return "de la voiture";
        case "bike":
          return "du vélo";
        case "trailer":
          return "de la remorque";
        default:
          return "de l'objet";
      }
    },
  },
  methods: {
    durationInHours,
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
};
</script>

<style lang="scss">
.loanable-details-box {
  .card-img-top {
    aspect-ratio: 16 / 10;
    object-fit: cover;
  }
  .user_text {
    // Show line feeds in comments, instructions and location_description
    white-space: pre-line;
  }
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
  dt {
    font-weight: 400;
    font-size: 0.8rem;
    color: $content-neutral-secondary;
    margin-bottom: 0.5rem;
  }
  dd {
    margin-bottom: 1rem;
  }
  dl {
    margin-bottom: 0;
  }
  hr {
    margin-top: 0;

    margin-bottom: 1rem;
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
}
</style>
