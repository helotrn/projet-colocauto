<template>
  <div class="position-relative">
    <b-card :class="{ archived: user.deleted_at }" class="overflow-hidden">
      <div class="user-blurb-row" :class="{ 'has-buttons': $slots.buttons }">
        <div class="user-blurb-text">
          <div class="center">
            <user-avatar :user="user"></user-avatar>
          </div>
          <div class="center">
            <span>
              <template v-if="showAdminLink && userLink">
                <router-link class="user-blurb-name" :to="userLink">
                  {{ user.full_name }} <slot name="nameicon"></slot>
                </router-link>
              </template>
              <span v-else class="user-blurb-name">
                {{ user.full_name }} <slot name="nameicon"></slot>
              </span>

              <span v-if="showPhone"
                ><br />
                <b-link :href="`tel:${numberOnlyPhone(user.phone)}`" target="_blank">
                  {{ user.phone }}
                </b-link></span
              ><br v-if="showEmail" />
              <a
                v-if="showEmail"
                class="text-nowrap"
                target="_blank"
                :href="`mailto:${user.email}`"
                >{{ user.email }}</a
              >
            </span>

            <slot name="description">
              {{ description }}
            </slot>
          </div>
        </div>
        <slot name="buttons"></slot>
      </div>
    </b-card>
    <b-dropdown
      v-if="$slots.dropdown"
      class="user-blurb-dropdown"
      size="sm"
      variant="white-primary"
      no-caret
      toggle-class="mr-0"
      right
    >
      <template #button-content>
        <b-icon icon="three-dots-vertical" /><span class="sr-only">actions</span>
      </template>
      <slot name="dropdown"></slot>
    </b-dropdown>
  </div>
</template>

<script>
import UserAvatar from "@/components/User/Avatar.vue";
import { numberOnlyPhone } from "@/helpers/filters";

export default {
  name: "UserBlurb",
  components: { UserAvatar },
  props: {
    user: {
      type: Object,
      required: false,
      default: () => ({}),
    },
    showPhone: {
      type: Boolean,
      default: false,
    },
    showEmail: {
      type: Boolean,
      default: false,
    },
    showAdminLink: {
      type: Boolean,
      default: false,
    },
    description: {
      type: String,
      default: "",
    },
  },
  computed: {
    userLink() {
      if (this.user.deleted_at) {
        return null;
      }
      return `/admin/users/${this.user.id}`;
    },
  },
  methods: {
    numberOnlyPhone,
  },
};
</script>

<style scoped lang="scss">
@import "~bootstrap/scss/mixins/breakpoints";
.card {
  border: 1px solid $light-grey;
}
.card-body {
  padding: 0;
  overflow: auto;
}
.center {
  display: flex;
  justify-content: center;
  flex-direction: column;
}
.archived {
  background: $eggshell;
  opacity: 0.5;
  a,
  span {
    color: $content-neutral-secondary;
  }
}
.user-blurb-row {
  padding: 0.75rem;
  display: flex;
  justify-content: space-between;
  gap: 0.75rem;
  min-width: min-content;
  &:not(.has-buttons) {
    flex-wrap: nowrap;
  }

  @include media-breakpoint-down(sm) {
    flex-wrap: wrap;
  }
}
.user-blurb-text {
  display: flex;
  flex-wrap: nowrap;
  gap: 0.875em;
  font-size: 0.925rem;
}
.user-blurb-dropdown {
  position: absolute;
  top: 0;
  right: 0;
}

.user-blurb-name {
  font-size: 1rem;
  font-weight: 600;
}
</style>
