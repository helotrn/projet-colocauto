<template>
  <div class="date-range-picker">
    <date-picker v-model="from" clear-button />
    <span>:</span>
    <date-picker v-model="to" clear-button />
  </div>
</template>

<script>
import DatePicker from '@/components/Forms/DatePicker.vue';
import dayjs from 'dayjs';

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
        if (val || this.to) {
          this.$emit('input', `${val ? dayjs(val).toISOString() : ''}@${this.to ? dayjs(this.to).add(1, 'day').toISOString() : ''}`);
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
        return this.value.match(/.*@(.*?)T/) ? dayjs(this.value.match(/.*@(.*?)T/)[1]).subtract(1, 'day').format('YYYY-M-D') : null;
      },
      set(val) {
        if (this.from || val) {
          this.$emit('input', `${this.from ? dayjs(this.from).toISOString() : ''}@${val ? dayjs(val).add(1, 'day').toISOString() : ''}`);
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
