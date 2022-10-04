<template>
  <div class="loanable-details-box">
    <b-card :img-src="loanable.image ? loanable.image.url : ''" img-top>
      <b-card-title class="mb-2">
        <a v-if="loanableUrl" :href="loanableUrl">{{ loanable.name }}</a>
        <span v-else>{{ loanable.name }}</span>
      </b-card-title>
      <div class="details-toggle mb-3 text-muted" v-b-toggle.loanable-details>
        Détails {{ prettyType }}
        <b-icon v-if="loadedFullLoanable" icon="caret-right-fill"></b-icon>
      </div>
      <b-card-text>
        <b-collapse id="loanable-details" role="tabpanel" accordion="loanable-details">
          <layout-loading v-if="!loadedFullLoanable"></layout-loading>
          <div v-else>
            <div v-if="loanable.type === 'bike'">
              <dl>
                <dt>{{ $t("fields.model") | capitalize }}</dt>
                <dd>{{ loanable.model }}</dd>
                <dt>{{ $t("fields.bike_type") | capitalize }}</dt>
                <dd>{{ $t(`bike_types.${loanable.bike_type}`) | capitalize }}</dd>
                <dt>{{ $t("fields.size") | capitalize }}</dt>
                <dd>
                  {{ $t(`sizes.${loanable.size}`) | capitalize }}
                </dd>
              </dl>
            </div>
            <div v-else-if="loanable.type === 'trailer'">
              <dl>
                <dt>{{ $t("fields.maximum_charge") | capitalize }}</dt>
                <dd>{{ loanable.maximum_charge }}</dd>
              </dl>
            </div>
            <div v-else-if="loanable.type === 'car'">
              <dl>
                <dt>{{ $t("fields.brand") | capitalize }}</dt>
                <dd>{{ loanable.brand }}</dd>
                <dt>{{ $t("fields.model") | capitalize }}</dt>
                <dd>{{ loanable.model }}</dd>
                <dt>{{ $t("fields.year_of_circulation") | capitalize }}</dt>
                <dd>{{ loanable.year_of_circulation }}</dd>
                <dt>{{ $t("fields.transmission_mode") | capitalize }}</dt>
                <dd>{{ $t(`transmission_modes.${loanable.transmission_mode}`) | capitalize }}</dd>
                <dt>{{ $t("fields.engine") | capitalize }}</dt>
                <dd>{{ $t(`engines.${loanable.engine}`) | capitalize }}</dd>
                <dt>{{ $t("fields.papers_location") | capitalize }}</dt>
                <dd>{{ $t(`papers_locations.${loanable.papers_location}`) | capitalize }}</dd>
              </dl>
            </div>
          </div>
        </b-collapse>
        <hr />
        <template v-if="!isOwner || ownerUrl">
          <dl>
            <dt>Propriétaire</dt>
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
            <dt>Emprunteur-se</dt>
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
            <dt>Début</dt>
            <dd>
              {{ loan.departure_at | shortDate | capitalize }}<br />{{ loan.departure_at | time }}
            </dd>
          </b-col>
          <b-col tag="dl" cols="6">
            <dt>Fin</dt>
            <dd>
              {{ returnAt | shortDate | capitalize }}<br />
              {{ returnAt | time }}
            </dd>
          </b-col>
        </b-row>
        <b-row>
          <b-col tag="dl" cols="6">
            <dt>Durée</dt>
            <dd v-if="duration > 0">
              {{ duration | durationInHours }}
              <span v-if="loan.calendar_days > 1"> sur {{ loan.calendar_days }} jours</span>
            </dd>
          </b-col>
          <b-col tag="dl" cols="6" v-if="price > 0">
            <dt>Distance <span v-if="!hasFinalDistance">estimée</span></dt>
            <dd v-if="distance > 0">{{ distance }} km</dd>
          </b-col>
          <b-col cols="12" v-if="price > 0 || insurance > 0">
            <dt class="mb-2 details-toggle" v-b-toggle.price-details>
              Coût <span v-if="!loan.final_price">estimé</span>&nbsp;
              <b-icon icon="caret-right-fill"></b-icon>
            </dt>
            <dd>
              <layout-loading inline v-if="loanLoading"></layout-loading>
              <template v-else>
                <div v-if="showCostSummary" class="font-weight-bold">
                  <div v-if="isLoanAdmin">
                    Montant transféré entre utilisateurs: {{ ownerTotal | currency }}<br />
                    total pour l'emprunteur-se: {{ borrowerTotal | currency }}
                  </div>
                  <div v-else-if="isBorrower">{{ borrowerTotal | currency }}</div>
                  <div v-else-if="isOwner">{{ ownerTotal | currency }}</div>
                </div>
                <b-collapse
                  @input="(visible) => (showCostSummary = !visible)"
                  :visible="isLoanAdmin"
                  id="price-details"
                  role="tabpanel"
                  accordion="price-details"
                >
                  <table class="price-table">
                    <tr>
                      <th>Temps et distance</th>
                      <td class="text-right tabular-nums">{{ price | currency }}</td>
                    </tr>
                    <tr>
                      <th :class="{ 'pb-2': !isBorrower }">Dépenses déduites</th>
                      <td class="text-right tabular-nums">
                        {{ -purchasesAmount | currency }}
                      </td>
                    </tr>
                    <tr v-if="!isBorrower" class="total-row">
                      <th v-if="isLoanAdmin">Montant transféré entre utilisateurs</th>
                      <th v-else-if="isOwner">Montant reçu</th>
                      <td
                        v-if="isOwner || isLoanAdmin"
                        class="text-right tabular-nums border-top border-dark"
                      >
                        {{ ownerTotal | currency }}
                      </td>
                    </tr>
                    <template v-if="isBorrower || isLoanAdmin">
                      <tr>
                        <th>Assurances</th>
                        <td class="text-right tabular-nums">
                          {{ insurance | currency }}
                        </td>
                      </tr>
                      <tr>
                        <th class="pb-2">
                          Contribution volontaire
                          <div v-if="!loan.final_platform_tip" class="small muted">
                            Pourra être modifié lors du paiement final.
                          </div>
                        </th>
                        <td class="text-right tabular-nums pb-2">
                          {{ tip | currency }}
                        </td>
                      </tr>
                      <tr class="total-row">
                        <th>Total <span v-if="isLoanAdmin"> pour l'emptrunteur-se</span></th>
                        <td class="text-right tabular-nums border-top border-dark">
                          {{ borrowerTotal | currency }}
                        </td>
                      </tr>
                    </template>
                  </table>
                  <div class="small">
                    <a href="https://mailchi.mp/solon-collectif/tarifs-locomotion" target="_blank"
                      >Explication de la tarification</a
                    >
                  </div>
                </b-collapse>
              </template>
            </dd>
          </b-col>
        </b-row>
        <hr />

        <dl class="mb-0">
          <template v-if="loanable.comments">
            <dt>{{ $t("fields.comments") | capitalize }}</dt>
            <dd class="user_text">{{ loanable.comments }}</dd>
          </template>

          <template v-if="showInstructions && loanable.instructions">
            <dt>{{ $t("fields.instructions") | capitalize }}</dt>

            <dd class="user_text">{{ loanable.instructions }}</dd>
          </template>

          <dt>Emplacement</dt>
          <dd>
            <div class="mb-2">{{ loanable.location_description }}</div>
            <forms-map-input v-if="loanable.position" :value="loanable.position" disabled bounded />
          </dd>
        </dl>
      </b-card-text>
    </b-card>
  </div>
</template>

<script>
import FormsMapInput from "@/components/Forms/MapInput.vue";

import locales from "@/locales";
import UserMixin from "@/mixins/UserMixin";

export default {
  name: "LoanDetailsBox",
  components: { FormsMapInput },
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
    loadedFullLoanable: {
      type: Boolean,
      required: true,
      default: true,
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
  i18n: {
    messages: {
      en: {
        ...locales.en.loanables,
      },
      fr: {
        ...locales.fr.loanables,
      },
    },
  },
};
</script>

<style lang="scss">
.loanable-details-box {
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
  .price-table {
    width: 100%;
    font-size: 0.8rem;
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
