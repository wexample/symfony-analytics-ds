<script>
import AbstractEntityTable from '@wexample/symfony-design-system/components/collection/table/abstract-entity-table/abstract-entity-table.vue';
import AbstractEntityCollectionVueMixin from '@wexample/symfony-design-system/js/Vue/AbstractEntityCollectionVueMixin';
import AnalyticsBreakdown from '@wexample/symfony-analytics/Entity/AnalyticsBreakdown';
import { analyticsAudienceFollow, analyticsCountryFlag, analyticsCountryName, analyticsPeriodFollow } from '../../js/Helper/AnalyticsPeriodHelper';

const T = 'WexampleSymfonyAnalyticsDsBundle.components.analytics-breakdown-table.analytics-breakdown-table::';
const METRIC = 'WexampleSymfonyAnalyticsBundle.common.analytics::metric.';

// The audience of the page's period broken down by one dimension — pages,
// sources, countries… —, the most visited first. Read once the period-nav of
// the page has said its period, and again at each change; of the audience
// the audience-nav says, unless the table is given one (`audience`). The
// robot dimension has their pages alone: a declared robot has neither
// visitor nor visit.
export default {
  extends: AbstractEntityTable,

  template: '#vue-template-wexample-symfony-analytics-ds-bundle-components-analytics-breakdown-table-analytics-breakdown-table',

  props: {
    dimension: {
      type: String,
      required: true
    },
    // `humans`, `robots` or `all` whatever the audience-nav says.
    audience: {
      type: String,
      default: null
    }
  },

  data() {
    return {
      pageLength: 10,
      showCount: true,
      // Two to a row in the page's grid: wider than its cell, it scrolls
      // sideways in it rather than running over the table beside it.
      scroll: true,
      defaultSort: { key: this.dimension === 'robot' ? 'views' : 'visits', direction: 'desc' },
      period: null,
      audienceFollowed: null
    };
  },

  mounted() {
    if (!this.audience) {
      this.forgetAudience = analyticsAudienceFollow((audience) => {
        this.audienceFollowed = audience;
        this.refreshEntitiesCollection();
      });
    }

    this.forgetPeriod = analyticsPeriodFollow((period) => {
      this.period = period;
      this.refreshEntitiesCollection();
    });
  },

  beforeUnmount() {
    this.forgetAudience?.();
    this.forgetPeriod?.();
  },

  methods: {
    getEntityClass() {
      return AnalyticsBreakdown;
    },

    getStateKey() {
      return `analytics.${this.dimension}`;
    },

    getCollectionQuery() {
      return {
        ...AbstractEntityTable.methods.getCollectionQuery.call(this),
        dimension: this.dimension,
        audience: this.audience ?? this.audienceFollowed ?? 'humans',
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
        case 'country':
          return `${analyticsCountryFlag(value)} ${analyticsCountryName(value)}`;
        case 'source':
        case 'device':
          return this.trans(`${T}${this.dimension}.${value}`);
        // A robot by its own name; the ones without one, and the reasons a
        // suspected one was caught, said in words.
        case 'robot':
          return ['other', 'cadence', 'burst', 'headers'].includes(value) ? this.trans(`${T}robot.${value}`) : value;
        default:
          return value;
      }
    },

    getColumnsConfiguration() {
      const metric = (key) => ({ key, label: this.trans(`${METRIC}${key}`), cell: 'number', align: 'right', sortable: true });

      // A page leads to itself: its key is the path it was read at.
      const key = this.dimension === 'page'
        ? { key: 'dimensionKey', label: this.trans(`${T}dimension.page`), href: (row) => row.dimensionKey }
        : { key: 'dimensionKey', label: this.trans(`${T}dimension.${this.dimension}`), cell: 'clamp', format: (value) => this.formatKey(value) };

      return this.dimension === 'robot'
        ? [key, metric('views')]
        : [key, metric('visitors'), metric('visits'), metric('views')];
    }
  }
};
</script>
