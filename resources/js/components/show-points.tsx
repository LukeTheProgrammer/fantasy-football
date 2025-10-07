import { c } from '@/lib/conv';

interface ShowPointsProps {
  value: number | string | null;
}

export default function ShowPoints({ value }: ShowPointsProps) {

  const val = c(value).toNumber();

  if (val > 0) {
    return (
      <span> {val} </span>
    );
  }

  return (
    <span> -- </span>
  );
}
