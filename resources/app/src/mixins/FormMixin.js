import { extractErrors } from "@/helpers";

export default {
  beforeRouteLeave(to, from, next) {
    const guard = from.meta.skipCleanup;
    if (!((typeof guard === "function" && guard(to, from)) || !!guard)) {
      this.$store.commit(`${this.slug}/item`, null);
      this.$store.commit(`${this.slug}/initialItem`, null);
    }

    next();
  },
  beforeRouteEnter(to, from, next) {
    next((vm) => {
      vm.loadItem();
    });
  },
  computed: {
    changed() {
      return this.initialItemJson !== JSON.stringify(this.item);
    },
    context() {
      return this.$store.state[this.slug];
    },
    error() {
      return this.context.error;
    },
    form() {
      return this.context.form || this.$route.meta.form;
    },
    initialItem() {
      return this.$store.getters[`${this.slug}/initialItem`];
    },
    initialItemJson() {
      return this.$store.getters[`${this.slug}/initialItemJson`];
    },
    item: {
      get() {
        return this.context.item;
      },
      set(item) {
        this.$store.commit(`${this.slug}/item`, item);
      },
    },
    loading() {
      return !!this.context.cancelToken;
    },
    params() {
      return this.$route.meta.params;
    },
    parentPath() {
      const parentPathParts = this.$route.path.split("/").filter((p) => !!p);
      parentPathParts.pop();
      return `/${parentPathParts.join("/")}`;
    },
    slug() {
      return this.$route.meta.slug;
    },
  },
  methods: {
    async destroy() {
      await this.$store.dispatch("paymentMethods/destroy", this.item.id);
      this.$router.push(this.parentPath);
    },
    async loadItem() {
      const { dispatch } = this.$store;

      if (this.$route.name === "Login") {
        // Redirected to login page, do nothing
        return;
      }

      try {
        if (
          !this.skipLoadItem ||
          (typeof this.skipLoadItem === "function" && !this.skipLoadItem())
        ) {
          if (this.id === "new") {
            await dispatch(`${this.slug}/loadEmpty`);
          } else {
            await dispatch(`${this.slug}/retrieveOne`, {
              id: this.id,
              params: this.params,
            });
          }
        }

        if (this.formMixinCallback && typeof this.formMixinCallback === "function") {
          await this.formMixinCallback();
        }
      } catch (e) {
        if (e.request) {
          switch (e.request.status) {
            case 401:
              this.$store.commit("addNotification", {
                content: `Erreur de chargement de données pour ${this.slug}`,
                title: `${this.slug}`,
                variant: "warning",
                type: "data",
              });
              console.log(e);
              break;
            case 404:
              this.$router.push(`/${this.parentPath}`);
              break;
            default:
              throw e;
          }
        }

        throw e;
      }
    },
    reset() {
      this.$store.commit(`${this.slug}/item`, this.initialItem);
    },
    async submit() {
      try {
        if (!this.item.id) {
          await this.$store.dispatch(`${this.slug}/createItem`, this.params);
          this.$router.replace(this.$route.fullPath.replace("new", this.item.id));
        } else {
          await this.$store.dispatch(`${this.slug}/updateItem`, this.params);
        }
        if (typeof this.afterSubmit === "function") {
          this.afterSubmit();
        }
      } catch (e) {
        if (e.request) {
          switch (e.request.status) {
            case 422:
              this.$store.commit("addNotification", {
                content: extractErrors(e.response.data).join(", "),
                title: "Erreur de sauvegarde",
                variant: "danger",
                type: "form",
              });
              break;
            default:
              throw e;
          }
        }

        throw e;
      }
    },
  },
  props: {
    id: {
      type: String,
      required: true,
    },
  },
  watch: {
    error(newError) {
      let variant;
      let title;
      let content;

      switch (newError.response.status) {
        case 400:
          content = "Erreur dans la requête.";
          title = "Mauvaise requête";
          variant = "warning";
          break;
        case 401:
          content = "Mot de passe ou courriel invalide.";
          title = "Erreur d'authentification";
          variant = "warning";
          break;
        case 403:
          content = "Vous n'avez pas les permissions nécessaires pour effectuer cette action.";
          title = "Permissions insuffisantes";
          variant = "warning";
          break;
        case 404:
          content = "La page que vous avez demandée est introuvable.";
          title = "Page introuvable";
          variant = "warning";
          break;
        case 500:
          content = "Une erreur système s'est produite.";
          title = "Erreur fatale";
          variant = "danger";
          break;
        default:
          return;
      }

      this.$store.commit("addNotification", {
        content,
        title,
        variant,
        type: this.slug,
      });
    },
    item: {
      deep: true,
      handler(newValue, oldValue) {
        if (oldValue === null && newValue) {
          if (this.$route.hash) {
            setTimeout(() => {
              const el = document.getElementById(this.$route.hash.substring(1));
              if (el) {
                this.$scrollTo(el);
              }
            }, 100);
          }
        }
        this.$store.commit(`${this.slug}/item`, this.item);
      },
    },
  },
};
