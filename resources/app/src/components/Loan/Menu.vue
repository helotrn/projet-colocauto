<template>
  <ul :class="{ 'loan-menu': true, horizontal, }">
    <li :class="{
      'current-step': isCurrentStep('creation'),
      'reached-step': hasReachedStep('creation'),
    }">
      <svg-danger v-if="hasCanceledStep('creation')" />
      <svg-check v-else-if="hasReachedStep('creation')" />
      <svg-waiting v-else />

      <span>Demande d'emprunt</span>
    </li>
    <li :class="{
      'current-step': isCurrentStep('intention'),
      'reached-step': hasReachedStep('intention'),
    }">
      <svg-danger v-if="hasCanceledStep('intention')" />
      <svg-check v-else-if="hasReachedStep('intention')" />
      <svg-waiting v-else />

      <span>Confirmation de l'emprunt</span>
    </li>
    <li :class="{
      'current-step': isCurrentStep('pre_payment'),
      'reached-step': hasReachedStep('pre_payment'),
    }">
      <svg-danger v-if="hasCanceledStep('pre_payment')" />
      <svg-check v-else-if="hasReachedStep('pre_payment')" />
      <svg-waiting v-else />

      <span>Prépaiement</span>
    </li>
    <li :class="{
      'current-step': isCurrentStep('takeover'),
      'reached-step': hasReachedStep('takeover'),
    }">
      <svg-danger v-if="hasCanceledStep('takeover')" />
      <svg-check v-else-if="hasReachedStep('takeover')" />
      <svg-waiting v-else />

      <span>Prise de possession</span>
    </li>
    <li v-for="incident in item.incidents" :key="incident.id">
      <span>Incident</span>
    </li>
    <li :class="{
      'current-step': isCurrentStep('handover'),
      'reached-step': hasReachedStep('handover'),
    }">
      <svg-danger v-if="hasCanceledStep('handover')" />
      <svg-check v-else-if="hasReachedStep('handover')" />
      <svg-waiting v-else />

      <span>Remise du véhicule</span></li>
    </li>
    <li :class="{
      'current-step': isCurrentStep('payment'),
      'reached-step': hasReachedStep('payment'),
    }">
      <svg-danger v-if="hasCanceledStep('payment')" />
      <svg-check v-else-if="hasReachedStep('payment')" />
      <svg-waiting v-else />

      <span>Conclusion</span></li>
    </li>
  </ul>
</template>

<script>
import Check from '@/assets/svg/check.svg';
import Danger from '@/assets/svg/danger.svg';
import Waiting from '@/assets/svg/waiting.svg';

import LoanStepsSequence from '@/mixins/LoanStepsSequence';

export default {
  name: 'Menu',
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
  },
  components: {
    'svg-check': Check,
    'svg-danger': Danger,
    'svg-waiting': Waiting,
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
