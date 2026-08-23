import { c } from '@/common/helpers/conv';

interface ShowPointsProps {
  value: number | string | null;
}

export function ShowPoints({ value }: ShowPointsProps) {

  const val = c(value).toNumber();

  if (val > 0) {
    return (
      <span> {val} </span>
    );
  }

  return (
    <span className="w-full text-center text-muted-foreground"> -- </span>
  );
}
