<template>
  <div>
    <div v-if="isTotallyBalanced" class="text-center w-75 m-auto">
      <icons-check class="rounded-green-circle" />
      Le compte est bon !<br/>
      Les participant·es de votre groupe sont à l'équilibre.<br/>
      <slash-illustration class="my-4"/>
    </div>
    <dl v-else>
    <div v-for="user in users" class="balance" :class="user.balance > 0 ? 'credit' : 'debit'">
      <dt>
        {{user.full_name}}
      </dt>
      <dd>
        <span>{{user.balance | currency }}</span>
      </dd>
    </div>
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
  dt {
    font-weight: normal;
    overflow: hidden;
    text-overflow: ellipsis;
  }
  dd {
    align-self: end;
  }
  .balance {
    display: flex;
    justify-content: space-between;
    padding: .625em 0;
  }
  .balance.debit {
    flex-direction: row-reverse;
  }
  dd span {
    padding: 5px 10px;
    border-radius: 5px;
    font-weight: bold;
  }
  .debit {
    dd span {
      background: #FFE6E4;
      color: #EB4335;
    }
    dt {
      text-align: right;
      margin-left: 1em;
    }
  }
  .credit {
    dd span {
      background: #E5F5F0;
      color: #34A853;
    }
    dt {
      margin-right: 1em;
    }
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
