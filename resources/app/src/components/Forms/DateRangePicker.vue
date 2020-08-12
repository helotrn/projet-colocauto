<template>
  <div class="date-range-picker">
    <date-picker v-model="from" clear-button />
    <span>:</span>
    <date-picker v-model="to" clear-button />
  </div>
</template>

<script>
import DatePicker from '@/components/Forms/DatePicker.vue';

export default {
  name: 'FormsDateRangePicker',
  components: {
    DatePicker,
  },
  props: {
    value: {
      required: false,
      type: String,
      default: ':',
    },
  },
  computed: {
    from: {
      get() {
        if (!this.value) {
          return null;
        }

        return this.value.split(':')[0];
      },
      set(val) {
        this.$emit('input', `${val || ''}:${this.to}`);
      },
    },
    to: {
      get() {
        if (!this.value) {
          return null;
        }

        return this.value.split(':')[1];
      },
      set(val) {
        this.$emit('input', `${this.from}:${val || ''}`);
      },
    },
  },
};
</script>

<style lang="scss">
.date-range-picker {
  display: flex;

  > span {
    flex-shrink: 1;
    margin: auto 8px;
  }
}
</style>
