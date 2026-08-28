import { useEffect, useState } from 'react';

/**
 * State that survives a page reload.
 *
 * The draft room reloads whenever a pick lands, and losing the position filter
 * or the search term mid auction costs the seconds that matter. Storage is per
 * key, so each draft keeps its own.
 */
export function usePersistentState<T>(key: string, initial: T) {
  const [value, setValue] = useState<T>(() => {
    if (typeof window === 'undefined') {
      return initial;
    }

    try {
      const stored = window.localStorage.getItem(key);

      return stored === null ? initial : (JSON.parse(stored) as T);
    } catch {
      // A browser with storage blocked still has to render a working board.
      return initial;
    }
  });

  useEffect(() => {
    try {
      window.localStorage.setItem(key, JSON.stringify(value));
    } catch {
      // Nothing to do: the value is still held in memory for this session.
    }
  }, [key, value]);

  return [value, setValue] as const;
}
