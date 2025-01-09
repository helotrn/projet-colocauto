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
        <template v-if="!isOwner || ownerUrl">
          <hr />
          <dl>
            <dt>{{ $t("loanable.fields.owner_id") | capitalize }}</dt>
            <dd class="owner-details">
              <a :href="ownerUrl" v-if="ownerUrl">
                {{ ownerName }}
              </a>
              <span v-else>{{ ownerName }}</span>
              <template v-if="loanable.owner && loanable.owner.user.email">
                <br />
                {{ loanable.owner.user.email }}
              </template>
              <template v-if="loanable.owner && loanable.owner.user.phone">
                <br />
                {{ loanable.owner.user.phone }}
              </template>
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
            <loan-price-details :loan="loan" :loan-loading="loanLoading" />
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

          <template v-if="loanable.location_description">
            <dt >{{ $t("loanable.fields.position") | capitalize }}</dt>
            <dd>
              <div class="mb-2">{{ loanable.location_description }}</div>
            </dd>
          </template>
        </dl>
      </b-card-text>
    </b-card>
  </div>
</template>

<script>
import { durationInHours } from "@/helpers/filters";

import locales from "@/locales";
import UserMixin from "@/mixins/UserMixin";
import LoanPriceDetails from "@/components/Loan/PriceDetails";

export default {
  name: "LoanDetailsBox",
  mixins: [UserMixin],
  components: {LoanPriceDetails},
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
    ownerUrl() {
      const ownerId = this.loan.loanable?.owner?.user.id;

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
      return this.user.id === this.loan.loanable?.owner?.user.id;
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
    ownerName() {
      if( this.loanable.owner ) {
        return this.loanable.owner.user.full_name;
      } else if( this.loanable.community ) {
        return `${this.loan.loanable.community.name} (${this.loanable.name})`;
      } else return this.loanable.name;
    }
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
}
</style>
