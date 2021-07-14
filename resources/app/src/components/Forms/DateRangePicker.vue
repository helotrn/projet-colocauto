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
        if (!this.value || this.value === ':') {
          return null;
        }
        return this.value.match(/(.*?)T.*@/) ? this.value.match(/(.*?)T.*@/)[1] : null;
      },
      set(val) {
        // Remove colon when no date selected, to avoid counting empty filter as active
        if (val || this.to) {
          this.$emit('input', `${val ? new Date(val).toISOString() : ''}@${this.to ? new Date(this.to).toISOString() : ''}`);
        } else {
          this.$emit('input', '');
        }
      },
    },
    to: {
      get() {
        if (!this.value || this.value === ':') {
          return null;
        }
        return this.value.match(/.*@(.*?)T/) ? this.value.match(/.*@(.*?)T/)[1] : null;
      },
      set(val) {
        // Remove colon when no date selected, to avoid counting empty filter as active
        if (this.from || val) {
          this.$emit('input', `${this.from ? new Date(this.from).toISOString() : ''}@${val ? new Date(val).toISOString() : ''}`);
        } else {
          this.$emit('input', '');
        }
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
