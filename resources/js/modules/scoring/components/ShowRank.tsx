import { c } from '@/common/helpers/conv';

interface ShowRankProps {
  value: number | string | null;
  prepend?: string | null;
  append?: string | null;
}

export function ShowRank({ value, prepend, append }: ShowRankProps) {
  const val = c(value).toNumber();

  if (val > 0) {
    return (
      <span>
        {' '}
        {prepend ? prepend : ''} {val} {append ? append : ''}{' '}
      </span>
    );
  }

  return <span className="w-full text-center text-muted-foreground"> -- </span>;
}
