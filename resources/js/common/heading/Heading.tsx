import { cn } from '@/common/helpers/cn';

type HeadingProps = {
  title: string;
  description?: string;
  rightContent?: React.ReactNode;
  containerClass?: string;
  headingClass?: string;
};

export function Heading({ title, description, rightContent, containerClass, headingClass }: HeadingProps) {
  return (
    <div className={cn('mb-6', containerClass)}>
      <div className={cn('mb-4 flex items-center justify-between', headingClass)}>
        <div>
          <h1 className="mb-2 text-3xl">{title}</h1>
          <p className="">{description || ''}</p>
        </div>
        {rightContent}
      </div>
    </div>
  );
}
