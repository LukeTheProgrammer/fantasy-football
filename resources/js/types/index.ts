export interface User {
  id: number;
  name: string;
  email: string;
  email_verified_at: string | null;
}

export interface PageProps {
  auth: {
    user: User | null;
  };
  errors: Record<string, string>;
  [key: string]: any;
}
