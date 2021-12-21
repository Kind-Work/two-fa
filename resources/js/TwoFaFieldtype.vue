<template>
  <div class="relative clearfix">
    <div v-if="state === 'error'">
      <p>{{ meta.error }}</p>
    </div>

    <div v-if="state === 'active' || state === 'disabling'" class="content">
      <p>{{ translations['twofa::activated.msg'] }}</p>

      <div class="input-group">
        <input
          type="number"
          v-model="secret"
          class="two-fa-input input-text"
          pattern="\d{6}"
          maxlength="6"
          minlength="6"
          step="1"
        />
        <button
          @click="deactivate"
          class="btn rounded-l-none"
          :disabled="isInValid || state === 'disabling'"
        >
          {{ translations['twofa::activated.button'] }}
        </button>
      </div>
    </div>

    <transition name="two-fa-fade">
      <div
        v-if="state === 'idle'"
        class="
          activate
          flex flex-col
          items-center
          justify-center
          absolute
          top-0
          right-0
          bottom-0
          left-0
          z-10
        "
      >
        <button @click="state = 'ready'" class="btn btn-primary">
          {{ translations['twofa::activate.enable.button'] }}
        </button>

        <div class="content pt-2">
          <p>{{ translations['twofa::activate.enable.description'] }}</p>
        </div>
      </div>
    </transition>

    <div
      v-if="state === 'idle' || state === 'ready' || state === 'activating'"
      class="activate-form"
    >
      <div class="float-left">
        <img v-if="isQrCodeImage" :src="meta.qrCode" />
        <div v-else v-html="meta.qrCode"></div>
      </div>

      <div class="right-side float-left p-2">
        <div class="content">
          <p class="break-all">
            <strong>{{ translations['twofa::activate.key_label'] }}:</strong>
            {{ meta.key }}
          </p>
          <p class="break-all">
            <strong>{{ translations['twofa::activate.url_label'] }}:</strong>
            {{ meta.url }}
          </p>
        </div>

        <div class="pt-2">
          <label class="publish-field-label pb-2">{{
            translations['twofa::activate.label']
          }}</label>

          <div class="input-group">
            <input
              type="number"
              v-model="secret"
              class="two-fa-input input-text"
              pattern="\d{6}"
              maxlength="6"
              minlength="6"
              step="1"
            />
            <button
              @click="activate"
              class="btn btn-primary rounded-l-none"
              :disabled="isInValid || state === 'activating'"
            >
              {{ translations['twofa::activate.button'] }}
            </button>
          </div>
        </div>

        <div class="content pt-2">
          <p>
            {{ translations['twofa::activate.get_app'] }}
            <button @click="showApps = 'android'">Android</button>,
            <button @click="showApps = 'ios'">iOS</button>.
          </p>

          <ul v-if="showApps === 'android'">
            <li v-for="n in 5" :key="n">
              <a
                :href="translations[`twofa::activate.android.${n}.url`]"
                target="_blank"
                >{{ translations[`twofa::activate.android.${n}.name`] }}</a
              >
            </li>
          </ul>

          <ul v-if="showApps === 'ios'">
            <li v-for="n in 5" :key="n">
              <a
                :href="translations[`twofa::activate.ios.${n}.url`]"
                target="_blank"
                >{{ translations[`twofa::activate.ios.${n}.name`] }}</a
              >
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
/*global Fieldtype*/

export default {
  mixins: [Fieldtype],
  data() {
    return {
      state: this.meta.state,
      data: this.value,
      secret: '',
      showApps: null,
    };
  },
  computed: {
    isInValid() {
      return /^\d{6}$/.test(this.secret) !== true;
    },
    translations() {
      return this.$store.state.statamic.config.translations;
    },
    isQrCodeImage() {
      return /^data/.test(this.meta.qrCode);
    },
  },
  methods: {
    activate() {
      this.state = 'activating';

      this.$axios
        .post(this.meta.actions.activate, {
          secret: this.secret,
          key: this.meta.key,
          id: this.meta.id,
        })
        .then((response) => {
          if (response.data.success === true) {
            this.$toast.success(this.activate.activated);
            this.data = this.meta.key;
            this.state = 'active';
          } else {
            this.$toast.error(this.translations['twofa::activate.erorrs.code']);
            this.state = 'idle';
          }
        })
        .catch(() => {
          this.$toast.error(
            this.translations['twofa::activate.errors.unknown'],
          );
          this.state = 'idle';
        })
        .finally(() => {
          this.secret = '';
          this.showApps = null;
        });
    },
    deactivate() {
      this.state = 'disabling';

      this.$axios
        .post(this.meta.actions.disable, {
          secret: this.secret,
          id: this.meta.id,
        })
        .then((response) => {
          if (response.data.success === true) {
            this.$toast.success(
              this.translations['twofa::deactivate.disabled'],
            );
            this.data = null;
            this.state = 'idle';
          } else {
            this.$toast.error(
              this.translations['twofa::deactivate.errors.code'],
            );
            this.state = 'active';
          }
        })
        .catch(() => {
          this.$toast.error(
            this.translations['twofa::deactivate.errors.unknown'],
          );
          this.state = 'active';
        })
        .finally(() => {
          this.secret = '';
        });
    },
  },
};
</script>

<style lang="scss" scoped>
// Layout
.right-side {
  @media (min-width: 768px) {
    width: calc(100% - 200px);
  }
}
.activate {
  background-color: rgba(255, 255, 255, 0.85);
}
.activate-form {
  transition: 480ms filter ease-in-out 12ms;
}
.activate + .activate-form {
  filter: blur(0.125em);
}

// For some reason this is not in Tailwind from Statamic
.break-all {
  word-break: break-all !important;
}
.top-0 {
  top: 0 !important;
}
.right-0 {
  right: 0 !important;
}
.bottom-0 {
  bottom: 0 !important;
}
.left-0 {
  left: 0 !important;
}
</style>
