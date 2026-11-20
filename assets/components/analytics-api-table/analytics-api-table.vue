<script>
import AbstractEntityTable from '@wexample/symfony-design-system/components/collection/table/abstract-entity-table/abstract-entity-table.vue';
import AbstractEntityCollectionVueMixin from '@wexample/symfony-design-system/js/Vue/AbstractEntityCollectionVueMixin';
import AnalyticsApiBreakdown from '@wexample/symfony-analytics/Entity/AnalyticsApiBreakdown';
import { periodFollow } from '@wexample/symfony-design-system/js/Helper/PeriodHelper';

const T = 'WexampleSymfonyAnalyticsDsBundle.components.analytics-api-table.analytics-api-table::';
const METRIC = 'WexampleSymfonyAnalyticsBundle.common.analytics::api_metric.';

// The calls to the api over the page's period broken down by one dimension —
// routes and MCP tools, callers, statuses, cache answers —, the most called
// first: how many, refused, stopped by a limit, failed, from the cache, how
// long. Read once the period-nav has said its period, and again at each change.
export default {
  extends: AbstractEntityTable,

  template: '#vue-template-wexample-symfony-analytics-ds-bundle-components-analytics-api-table-analytics-api-table',

  props: {
    dimension: {
      type: String,
      required: true
    }
  },

  data() {
    return {
      pageLength: 10,
      showCount: true,
      scroll: true,
      defaultSort: { key: 'calls', direction: 'desc' },
      period: null
    };
  },

  mounted() {
    this.forgetPeriod = periodFollow((period) => {
      this.period = period;
      this.refreshEntitiesCollection();
    });
  },

  beforeUnmount() {
    this.forgetPeriod?.();
  },

  methods: {
    getEntityClass() {
      return AnalyticsApiBreakdown;
    },

    getStateKey() {
      return `analytics.api.${this.dimension}`;
    },

    getCollectionQuery() {
      return {
        ...AbstractEntityTable.methods.getCollectionQuery.call(this),
        dimension: this.dimension,
        ...(this.period ?? {})
      };
    },

    // Nothing to read before the period is known: the first read waits for it.
    async readEntitiesCollection(options = {}) {
      if (!this.period) {
        return;
      }

      return AbstractEntityCollectionVueMixin.methods.readEntitiesCollection.call(this, options);
    },

    formatKey(value) {
      switch (this.dimension) {
        case 'cache':
          return this.trans(`${T}cache.${value || 'none'}`);
        // An anonymous caller is a hash under the day: said as such.
        case 'client':
          return value.startsWith('anonymous:') ? `${this.trans(`${T}anonymous`)} ${value.slice(10)}` : value;
        default:
          return value;
      }
    },

    getColumnsConfiguration() {
      const metric = (key, label, unit = null) => ({ key, label: this.trans(`${METRIC}${label}`), cell: 'number', align: 'right', sortable: true, ...(unit ? { unit } : {}) });

      return [
        { key: 'dimensionKey', label: this.trans(`${T}dimension.${this.dimension}`), cell: 'clamp', format: (value) => this.formatKey(value) },
        metric('calls', 'calls'),
        metric('clientErrors', 'client_errors'),
        metric('limited', 'limited'),
        metric('serverErrors', 'server_errors'),
        metric('cached', 'cached'),
        metric('averageMs', 'average_ms', 'ms'),
        metric('maxMs', 'max_ms', 'ms')
      ];
    }
  }
};
</script>
