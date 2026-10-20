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
