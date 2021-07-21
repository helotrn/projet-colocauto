<template>
  <ul :class="{ 'loan-menu': true, horizontal }">
    <li
      :class="{
        'current-step': isCurrentStep('creation'),
        'reached-step': hasReachedStep('creation'),
      }"
    >
      <svg-danger v-if="hasCanceledStep('creation')" />
      <svg-check v-else-if="hasReachedStep('creation')" />
      <svg-waiting v-else />

      <span>Demande d'emprunt</span>
    </li>
    <li
      :class="{
        'current-step': isCurrentStep('intention'),
        'reached-step': hasReachedStep('intention'),
      }"
      v-if="displayStep('intention')"
    >
      <svg-danger v-if="hasCanceledStep('intention')" />
      <svg-check v-else-if="hasReachedStep('intention')" />
      <svg-waiting v-else />

      <span>Confirmation de l'emprunt</span>
    </li>
    <li
      :class="{
        'current-step': isCurrentStep('pre_payment'),
        'reached-step': hasReachedStep('pre_payment'),
      }"
      v-if="displayStep('pre_payment')"
    >
      <svg-danger v-if="hasCanceledStep('pre_payment')" />
      <svg-check v-else-if="hasReachedStep('pre_payment')" />
      <svg-waiting v-else />

      <span>Prépaiement</span>
    </li>
    <li
      :class="{
        'current-step': isCurrentStep('takeover'),
        'reached-step': hasReachedStep('takeover'),
      }"
      v-if="displayStep('takeover')"
    >
      <svg-danger v-if="hasCanceledStep('takeover')" />
      <svg-check v-else-if="hasReachedStep('takeover')" />
      <svg-waiting v-else />

      <span>Informations avant de partir</span>
    </li>
    <li
      v-for="incident in item.incidents"
      :key="incident.id"
      :class="{
        'current-step': incident.status === 'in_process',
        'reached-step': incident.status === 'completed',
      }"
    >
      <svg-danger v-if="incident.status === 'canceled' || loanIsCanceled" />
      <svg-check v-else-if="incident.status === 'completed'" />
      <svg-waiting v-else />
      <span>Incident ({{ prettyIncidentName(incident.incident_type) }})</span>
    </li>
    <li
      v-for="extension in item.extensions"
      :key="extension.id"
      :class="{
        'current-step': extension.status === 'in_process',
        'reached-step': extension.status === 'completed',
      }"
    >
      <svg-danger v-if="extension.status === 'canceled' || loanIsCanceled" />
      <svg-check v-else-if="extension.status === 'completed'" />
      <svg-waiting v-else />
      <span>Retard</span>
    </li>
    <li
      :class="{
        'current-step': isCurrentStep('handover'),
        'reached-step': hasReachedStep('handover'),
      }"
      v-if="displayStep('handover')"
    >
      <svg-danger v-if="hasCanceledStep('handover')" />
      <svg-check v-else-if="hasReachedStep('handover')" />
      <svg-waiting v-else />

      <span>Retour</span>
    </li>
    <li
      :class="{
        'current-step': isCurrentStep('payment'),
        'reached-step': hasReachedStep('payment'),
      }"
      v-if="displayStep('payment')"
    >
      <svg-danger v-if="hasCanceledStep('payment') || hasActiveIncidents || hasActiveExtensions" />
      <svg-check v-else-if="hasReachedStep('payment')" />
      <svg-waiting v-else />

      <span>Conclusion</span>
    </li>
  </ul>
</template>

<script>
import Check from "@/assets/svg/check.svg";
import Danger from "@/assets/svg/danger.svg";
import Waiting from "@/assets/svg/waiting.svg";

import LoanStepsSequence from "@/mixins/LoanStepsSequence";

export default {
  name: "Menu",
  mixins: [LoanStepsSequence],
  props: {
    horizontal: {
      type: Boolean,
      required: false,
      default: false,
    },
    item: {
      type: Object,
      required: true,
    },
    user: {
      type: Object,
      required: true,
    },
  },
  components: {
    "svg-check": Check,
    "svg-danger": Danger,
    "svg-waiting": Waiting,
  },
  methods: {
    prettyIncidentName(type) {
      switch (type) {
        case "delay":
          return "Retard";
        case "accident":
          return "Accident";
        default:
          return "Générique";
      }
    },
  },
};
</script>

<style lang="scss">
.loan-menu {
  padding: 0;
  margin: 0;
  list-style-type: none;

  li {
    line-height: 19px;
    margin-bottom: 10px;

    opacity: 0.5;
  }

  li.current-step,
  li.reached-step {
    opacity: 1;
  }

  li span {
    position: relative;
    top: 5px;
  }

  li svg {
    width: 19px;
    height: 19px;
    margin-right: 6px;
  }

  &.horizontal {
    display: flex;
    flex-direction: row;
    flex-wrap: wrap;

    li {
      display: inline;
      margin-right: 10px;
      white-space: nowrap;
    }

    li:not(.reached-step):not(.current-step) {
      display: none;
    }
  }
}
</style>
