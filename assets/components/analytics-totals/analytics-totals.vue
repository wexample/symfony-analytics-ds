<script>
import AbstractEntityManipulatorVueMixin from '@wexample/symfony-design-system/js/Vue/AbstractEntityManipulatorVueMixin';
import AnalyticsBreakdown from '@wexample/symfony-analytics/Entity/AnalyticsBreakdown';
import { analyticsPeriodFollow } from '../../js/Helper/AnalyticsPeriodHelper';

const METRIC = 'WexampleSymfonyAnalyticsBundle.common.analytics::metric.';
const T = 'WexampleSymfonyAnalyticsDsBundle.components.analytics-totals.analytics-totals::';

// The figures of the page's period: visitors, visits, pages viewed, and the
// pages a visit reads — the `total` line of the breakdown.
export default {
  mixins: [AbstractEntityManipulatorVueMixin],

  template: '#vue-template-wexample-symfony-analytics-ds-bundle-components-analytics-totals-analytics-totals',

  data() {
    return {
      totals: null
    };
  },

  computed: {
    figures() {
      const value = (key) => this.totals?.[key] ?? null;
      const perVisit = value('visits') ? value('views') / value('visits') : null;

      return [
        { key: 'visitors', tone: 'blue-jeans', label: this.trans(`${METRIC}visitors`), value: this.number(value('visitors')) },
        { key: 'visits', tone: 'mint', label: this.trans(`${METRIC}visits`), value: this.number(value('visits')) },
        { key: 'views', tone: 'plum', label: this.trans(`${METRIC}views`), value: this.number(value('views')) },
        { key: 'per_visit', tone: 'sunflower', label: this.trans(`${T}per_visit`), value: this.number(perVisit, 1) }
      ];
    }
  },

  mounted() {
    this.forgetPeriod = analyticsPeriodFollow((period) => this.read(period));
  },

  beforeUnmount() {
    this.forgetPeriod?.();
  },

  methods: {
    getEntityClass() {
      return AnalyticsBreakdown;
    },

    async read(period) {
      const result = await this.getEntityRepository().fetchListPaginated({ query: { dimension: 'total', ...period }, length: 0 });
      const line = result.items[0];

      this.totals = line
        ? { visitors: line.getDataValue('visitors'), visits: line.getDataValue('visits'), views: line.getDataValue('views') }
        : null;
    },

    number(value, decimals = 0) {
      if (value === null) {
        return '—';
      }

      const locale = (document.documentElement.lang || undefined)?.replace('_', '-');

      return new Intl.NumberFormat(locale, { maximumFractionDigits: decimals, minimumFractionDigits: decimals }).format(value);
    }
  }
};
</script>
