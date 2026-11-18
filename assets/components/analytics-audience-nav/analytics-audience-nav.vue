<script>
const T = 'WexampleSymfonyAnalyticsDsBundle.components.analytics-audience-nav.analytics-audience-nav::';
const AUDIENCES = ['humans', 'robots', 'all'];
const HASH_KEY = 'audience';

let uidCounter = 0;

// Whose pages the tables and the figures of the page add up: the people,
// the robots, or everything that read the site. Kept in the address beside
// the period, and announced like it (`analytics-audience-change`, answered
// again on `analytics-audience-request`) to whatever follows it.
export default {
  template: '#vue-template-wexample-symfony-analytics-ds-bundle-components-analytics-audience-nav-analytics-audience-nav',

  data() {
    return {
      uid: `analytics-audience-nav-${++uidCounter}`,
      audiences: AUDIENCES,
      selected: this.fromHash() ?? AUDIENCES[0]
    };
  },

  mounted() {
    window.addEventListener('hashchange', this.onHashChange);
    document.addEventListener('analytics-audience-request', this.onRequest);
    this.announce();
  },

  beforeUnmount() {
    window.removeEventListener('hashchange', this.onHashChange);
    document.removeEventListener('analytics-audience-request', this.onRequest);
  },

  methods: {
    t(key) {
      return this.trans(`${T}${key}`);
    },

    select(audience) {
      this.selected = audience;

      const params = new URLSearchParams(window.location.hash.slice(1));
      params.set(HASH_KEY, audience);
      window.history.replaceState(window.history.state, '', `#${params.toString()}`);

      this.announce();
    },

    announce() {
      this.$el.dispatchEvent(new CustomEvent('analytics-audience-change', { bubbles: true, detail: { audience: this.selected } }));
    },

    fromHash() {
      const audience = new URLSearchParams(window.location.hash.slice(1)).get(HASH_KEY);

      return AUDIENCES.includes(audience) ? audience : null;
    },

    onRequest() {
      this.announce();
    },

    onHashChange() {
      const audience = this.fromHash();

      if (audience && audience !== this.selected) {
        this.selected = audience;
        this.announce();
      }
    }
  }
};
</script>
