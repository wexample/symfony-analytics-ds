<script>
import ApiService from '@wexample/symfony-loader/js/Services/ApiService';
import PollingVueMixin from '@wexample/symfony-design-system/js/Vue/PollingVueMixin';
import DateDisplay from '@wexample/symfony-design-system/components/date-display/date-display.vue';
import FilePath from '@wexample/symfony-design-system/components/file-path/file-path.vue';
import Bar from '@wexample/symfony-design-system/components/bar/bar.vue';

const T = 'WexampleSymfonyAnalyticsDsBundle.components.analytics-now.analytics-now::';
// The words of the breakdown: a robot's reason, a source's name.
const BREAKDOWN = 'WexampleSymfonyAnalyticsDsBundle.components.analytics-breakdown-table.analytics-breakdown-table::';
// What is going on now moves by the minute: read again as often, while the
// page is seen (the loader's PollingService).
const POLL_MS = 60_000;
// The latest pages shown: as many as stand beside the chart of the day.
const LATEST_SHOWN = 6;

// What is going on in the site now, drawn first from what the page was
// rendered with (`summary`, the shape of AnalyticsTodayService::summarize()),
// then read again from the api. A page lays its two parts where it wants
// them (`part`): the key figures — the people at it, this hour beside
// yesterday's, the busiest robot —, and the people's latest pages; both read
// once a minute between them (one key of the PollingService).
export default {
  mixins: [PollingVueMixin],

  components: {
    Bar,
    DateDisplay,
    FilePath
  },

  template: '#vue-template-wexample-symfony-analytics-ds-bundle-components-analytics-now-analytics-now',

  props: {
    summary: {
      type: Object,
      required: true
    },
    // `figures` or `latest`.
    part: {
      type: String,
      default: 'figures'
    }
  },

  data() {
    return {
      now: this.summary
    };
  },

  computed: {
    // The latest pages a row each, as many as stand beside the chart.
    latest() {
      return (this.now.latest ?? []).slice(0, LATEST_SHOWN);
    },

    // How this hour moved against yesterday's: its sign, and which way.
    hourTrend() {
      const delta = (this.now.hour.now ?? 0) - (this.now.hour.yesterday ?? 0);

      return {
        label: `${delta > 0 ? '+' : ''}${this.number(delta)}`,
        direction: delta > 0 ? 'up' : (delta < 0 ? 'down' : null)
      };
    },

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
