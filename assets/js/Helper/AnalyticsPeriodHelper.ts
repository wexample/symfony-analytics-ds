// The period a period-nav announces (`period-change`, ISO bounds), followed
// by whatever reads the audience: it asks to be told it once listening
// (`period-request`), the period-nav having announced it before or after.
export type AnalyticsPeriod = { from: string; to: string };

export function analyticsPeriodFollow(onChange: (period: AnalyticsPeriod) => void): () => void {
  let current: AnalyticsPeriod | null = null;
  const listener = (event: Event): void => {
    const { from, to } = (event as CustomEvent<AnalyticsPeriod>).detail;

    if (current?.from !== from || current?.to !== to) {
      current = { from, to };
      onChange(current);
    }
  };

  document.addEventListener('period-change', listener);
  document.dispatchEvent(new CustomEvent('period-request'));

  return () => document.removeEventListener('period-change', listener);
}

// Whose pages the page adds up — `humans`, `robots`, `all` —, as the
// audience-nav announces it (`analytics-audience-change`), followed the same
// way as the period.
export function analyticsAudienceFollow(onChange: (audience: string) => void): () => void {
  let current: string | null = null;
  const listener = (event: Event): void => {
    const { audience } = (event as CustomEvent<{ audience: string }>).detail;

    if (current !== audience) {
      current = audience;
      onChange(audience);
    }
  };

  document.addEventListener('analytics-audience-change', listener);
  document.dispatchEvent(new CustomEvent('analytics-audience-request'));

  return () => document.removeEventListener('analytics-audience-change', listener);
}

// A country code as a flag: the two regional indicator letters it spells.
export function analyticsCountryFlag(code: string): string {
  return /^[A-Z]{2}$/.test(code)
    ? String.fromCodePoint(...[...code].map((letter) => 0x1f1e6 + letter.charCodeAt(0) - 65))
    : '';
}

export function analyticsCountryName(code: string): string {
  const locale = (document.documentElement.lang || 'en').replace('_', '-');

  try {
    return new Intl.DisplayNames([locale], { type: 'region' }).of(code) ?? code;
  } catch {
    return code;
  }
}
