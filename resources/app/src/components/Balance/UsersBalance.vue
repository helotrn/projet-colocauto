<template>
  <dl>
    <template v-for="user in users">
      <dt :class="user.balance > 0 ? 'credit' : 'debit'">
        {{user.full_name}}
      </dt>
      <dd
        :class="user.balance > 0 ? 'credit' : 'debit'"
        :style="`--width:${Math.min(100, Math.abs(user.balance) * 100 / sum)}%`"
      >
        {{user.balance}} €
      </dd>
    </template>
  </dl>
</template>

<script>
export default {
  name: "UsersBalance",
  props: {
    users: {
      type: Array,
      required: true,
    }
  },
  computed: {
    sum(){
      if(this.users.length == 0) return 0;
      return this.users.reduce((sum, u) => {
        if( u.balance > 0 ) sum += parseFloat(u.balance);
        return sum;
      }, 0);
    },
  }
}
</script>

<style lang="scss" scoped>
  dl {
    display: grid;
    grid-template-columns: 1fr 1fr;
    column-gap: 10px;
    row-gap: 10px;
  }
  dt.debit {
    position: relative;
    left: calc(100% + 10px);
  }
  dd {
    position: relative;
  }
  dd.debit {
    left: calc(-100% - 10px);
    text-align: right;
  }
  dt.credit {
    text-align: right;
  }
  dt, dd {
    margin: 10px 0;
  }
  dd {
    padding: 0 10px;
  }
  dd:after {
    content: '';
    width: var(--width, 20%);
    height: 100%;
    opacity: 0.3;
    height: calc(100% + 20px);
    opacity: 0.3;
    display: block;
    position: absolute;
    top: -10px;
    border-radius: 5px;
  }
  dd.debit:after {
    background: #EB4335;
    right: 0;
  }
  dd.credit:after {
    background: #4FB999;
    left: 0;
  }
</style>
