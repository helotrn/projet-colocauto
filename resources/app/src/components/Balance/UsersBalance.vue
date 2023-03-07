<template>
  <div>
    <div v-if="isTotallyBalanced" class="text-center w-75 m-auto">
      <icons-check class="rounded-green-circle" />
      Le compte est bon !<br/>
      Les participant·es de votre groupe sont à l'équilibre.<br/>
      <slash-illustration class="my-4"/>
    </div>
    <dl v-else>
    <template v-for="user in users">
      <dt :class="user.balance > 0 ? 'credit' : 'debit'">
        {{user.full_name}}
      </dt>
      <dd
        :class="user.balance > 0 ? 'credit' : 'debit'"
        :style="`--width:${Math.min(100, Math.abs(user.balance) * 100 / sum)}%`"
      >
        {{user.balance | currency }}
      </dd>
    </template>
    </dl>
  </div>
</template>

<script>
import IconsCheck from "@/assets/icons/check.svg";
import SlashIllustration from "@/assets/svg/slash_illustration-colocauto.svg";

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
    isTotallyBalanced() {
      return this.users.every(u => u.balance == 0);
    }
  },
  components: {
    IconsCheck,
    SlashIllustration,
  },
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
  .rounded-green-circle {
    border-radius: 50%;
    fill: white;
    background-color: $locomotion-light-green;
    display: block;
    margin: auto;
    margin-bottom: 1em;
    width: 40px;
    height: 40px;
    padding: 8px;
  }
</style>
