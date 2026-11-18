// Whose pages the page adds up — `humans`, `robots`, `all` —, as the
// audience-nav announces it (`analytics-audience-change`), followed as the
// period of the page is (the design system's periodFollow()): what starts
// listening after the nav announced it asks to be told again
// (`analytics-audience-request`).
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
