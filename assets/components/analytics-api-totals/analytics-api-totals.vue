<script>
import AbstractEntityManipulatorVueMixin from '@wexample/symfony-design-system/js/Vue/AbstractEntityManipulatorVueMixin';
import AnalyticsApiBreakdown from '@wexample/symfony-analytics/Entity/AnalyticsApiBreakdown';
import { periodFollow } from '@wexample/symfony-design-system/js/Helper/PeriodHelper';

const METRIC = 'WexampleSymfonyAnalyticsBundle.common.analytics::api_metric.';

// The figures of the calls to the api over the page's period: how many, by
// how many callers, how many stopped by a limit or failed, how fast — the
// `total` line of the api's breakdown.
export default {
  mixins: [AbstractEntityManipulatorVueMixin],

  template: '#vue-template-wexample-symfony-analytics-ds-bundle-components-analytics-api-totals-analytics-api-totals',

  data() {
    return {
      totals: null
    };
  },

  computed: {
    figures() {
      const value = (key) => this.totals?.[key] ?? null;
      const errors = this.totals ? value('clientErrors') + value('serverErrors') : null;

      return [
        { key: 'calls', tone: 'blue-jeans', label: this.trans(`${METRIC}calls`), value: this.number(value('calls')) },
        { key: 'clients', tone: 'mint', label: this.trans(`${METRIC}clients`), value: this.number(value('clients')) },
        { key: 'limited', tone: 'lavender', label: this.trans(`${METRIC}limited`), value: this.number(value('limited')) },
        { key: 'errors', tone: 'ruby', label: `${this.trans(`${METRIC}client_errors`)} / ${this.trans(`${METRIC}server_errors`)}`, value: errors === null ? '—' : `${this.number(value('clientErrors'))} / ${this.number(value('serverErrors'))}` },
        { key: 'average_ms', tone: 'sunflower', label: this.trans(`${METRIC}average_ms`), value: value('averageMs') === null ? '—' : `${this.number(value('averageMs'))} ms` }
      ];
    }
  },

  mounted() {
    this.forgetPeriod = periodFollow((period) => this.read(period));
  },

  beforeUnmount() {
    this.forgetPeriod?.();
  },

  methods: {
    getEntityClass() {
      return AnalyticsApiBreakdown;
    },

    async read(period) {
      const result = await this.getEntityRepository().fetchListPaginated({ query: { dimension: 'total', ...period }, length: 0 });
      const line = result.items[0];

      this.totals = line
        ? Object.fromEntries(['calls', 'clients', 'limited', 'clientErrors', 'serverErrors', 'averageMs'].map((key) => [key, line.getDataValue(key)]))
        : null;
    },

    number(value) {
      if (value === null) {
        return '—';
      }

      const locale = (document.documentElement.lang || undefined)?.replace('_', '-');

      return new Intl.NumberFormat(locale).format(value);
    }
  }
};
</script>
