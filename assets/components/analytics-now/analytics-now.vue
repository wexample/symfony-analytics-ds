<script>
import ApiService from '@wexample/symfony-loader/js/Services/ApiService';
import PollingVueMixin from '@wexample/symfony-design-system/js/Vue/PollingVueMixin';

const T = 'WexampleSymfonyAnalyticsDsBundle.components.analytics-now.analytics-now::';
// The words of the breakdown: a robot's reason, a source's name.
const BREAKDOWN = 'WexampleSymfonyAnalyticsDsBundle.components.analytics-breakdown-table.analytics-breakdown-table::';
// What is going on now moves by the minute: read again as often, while the
// page is seen (the loader's PollingService).
const POLL_MS = 60_000;

// What is going on in the site now — the people at it, this hour beside
// yesterday's, the busiest robot, the people's latest pages —, drawn first
// from what the page was rendered with (`summary`, the shape of
// AnalyticsTodayService::summarize()), then read again from the api.
export default {
  mixins: [PollingVueMixin],

  template: '#vue-template-wexample-symfony-analytics-ds-bundle-components-analytics-now-analytics-now',

  props: {
    summary: {
      type: Object,
      required: true
    }
  },

  data() {
    return {
      now: this.summary
    };
  },

  computed: {
    busiestRobot() {
      const busiest = this.now.busiest;

      if (!busiest) {
        return null;
      }

      // A suspected robot is named by why it was caught; a declared one by
      // its own name, unless it has none.
      return busiest.suspect || busiest.robot === 'other'
        ? this.trans(`${BREAKDOWN}robot.${busiest.robot}`)
        : busiest.robot;
    }
  },

  mounted() {
    this.poll({
      key: 'analytics-today',
      intervalMs: POLL_MS,
      read: async () => (await this.app.getServiceOrFail(ApiService).getClient().get({ path: 'analytics-today/summary' }).json())?.data,
      onResult: (summary) => {
        if (summary) {
          this.now = summary;
        }
      }
    });
  },

  methods: {
    t(key, args) {
      return this.trans(`${T}${key}`, args);
    },

    number(value) {
      return new Intl.NumberFormat(this.locale()).format(value ?? 0);
    },

    time(at) {
      return new Date(at).toLocaleTimeString(this.locale(), { hour: '2-digit', minute: '2-digit' });
    },

    source(view) {
      return this.trans(`${BREAKDOWN}source.${view.source}`);
    },

    country(code) {
      try {
        return new Intl.DisplayNames([this.locale()], { type: 'region' }).of(code) ?? code;
      } catch {
        return code;
      }
    },

    locale() {
      return document.documentElement.lang || undefined;
    }
  }
};
</script>
