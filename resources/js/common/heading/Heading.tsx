type HeadingProps = {
  title: string;
  description?: string;
  rightContent?: React.ReactNode;
};

export function Heading({ title, description, rightContent }: HeadingProps) {
  return (
    <div className="mb-6">
      <div className="mb-4 flex items-center justify-between">
        <div>
          <h1 className="mb-2 text-3xl">{title}</h1>
          <p className="">{description || ''}</p>
        </div>
        {rightContent}
      </div>
    </div>
  );
}
