// Type declarations for @inertiajs/react
import { AxiosInstance, AxiosRequestConfig } from 'axios';
import { Auth, SharedData } from './index';

declare module '@inertiajs/react' {
  export interface PageProps {
    auth?: Auth;
    errors?: Record<string, string>;
    [key: string]: any;
  }

  export interface Page<CustomPageProps extends PageProps = PageProps> {
    component: string;
    props: CustomPageProps;
    url: string;
    version: string | null;
    scrollRegions: Array<{ top: number; left: number }>;
    rememberedState: Record<string, unknown>;
  }

  export type InertiaFormErrors<TForm> = Partial<Record<keyof TForm, string>>;

  export interface FormProps<TForm> {
    data: TForm;
    errors: InertiaFormErrors<TForm>;
    hasErrors: boolean;
    processing: boolean;
    progress: {
      percentage: number | null;
      size: number | null;
      totalSize: number | null;
    } | null;
    wasSuccessful: boolean;
    recentlySuccessful: boolean;
    setData: {
      (key: keyof TForm, value: any): void;
      (values: Partial<TForm>): void;
      (callback: (data: TForm) => TForm): void;
    };
    transform: (callback: (data: TForm) => any) => void;
    reset: (...fields: Array<keyof TForm>) => void;
    clearErrors: (...fields: Array<keyof TForm>) => void;
    isDirty: boolean;
    submit: (method: 'get' | 'post' | 'put' | 'patch' | 'delete', url: string, options?: {
      headers?: Record<string, string>;
      onBefore?: () => boolean | void;
      onCancelToken?: (cancelToken: any) => void;
      onCancel?: () => void;
      onStart?: () => void;
      onProgress?: (progress: FormProps<TForm>['progress']) => void;
      onSuccess?: (page: Page) => void | boolean;
      onError?: (errors: InertiaFormErrors<TForm>) => void;
      onFinish?: () => void;
      preserveScroll?: boolean | ((props: Page['props']) => boolean);
      preserveState?: boolean | ((props: Page['props']) => boolean) | null;
      resetOnSuccess?: boolean;
      only?: string[];
    }) => void;
    get: (url: string, options?: Parameters<FormProps<TForm>['submit']>[2]) => void;
    post: (url: string, options?: Parameters<FormProps<TForm>['submit']>[2]) => void;
    put: (url: string, options?: Parameters<FormProps<TForm>['submit']>[2]) => void;
    patch: (url: string, options?: Parameters<FormProps<TForm>['submit']>[2]) => void;
    delete: (url: string, options?: Parameters<FormProps<TForm>['submit']>[2]) => void;
  }

  export function useForm<TForm extends Record<string, any>>(initialValues?: TForm): FormProps<TForm>;

  export function Head(props: { title?: string; children?: React.ReactNode }): JSX.Element;

  export function usePage<CustomPageProps extends PageProps = PageProps>(): Page<CustomPageProps>;

  export function Link(props: {
    as?: string;
    data?: Record<string, any>;
    href: string;
    method?: 'get' | 'post' | 'put' | 'patch' | 'delete';
    headers?: Record<string, string>;
    onClick?: (event: React.MouseEvent<HTMLAnchorElement>) => void;
    preserveScroll?: boolean | ((props: Page['props']) => boolean);
    preserveState?: boolean | ((props: Page['props']) => boolean) | null;
    replace?: boolean;
    only?: string[];
    onCancelToken?: (cancelToken: any) => void;
    onBefore?: () => boolean | void;
    onStart?: () => void;
    onProgress?: (progress: { percentage: number }) => void;
    onFinish?: () => void;
    onCancel?: () => void;
    onSuccess?: (page: Page) => void | boolean;
    onError?: (errors: Record<string, string>) => void;
    [key: string]: any;
  }): JSX.Element;

  export function router(): {
    visit: (
      url: string,
      options?: {
        method?: 'get' | 'post' | 'put' | 'patch' | 'delete';
        data?: Record<string, any>;
        replace?: boolean;
        preserveState?: boolean | ((page: Page) => boolean) | null;
        preserveScroll?: boolean | ((page: Page) => boolean);
        only?: string[];
        headers?: Record<string, string>;
        onCancelToken?: (cancelToken: any) => void;
        onBefore?: () => boolean | void;
        onStart?: () => void;
        onProgress?: (progress: { percentage: number }) => void;
        onSuccess?: (page: Page) => void | boolean;
        onError?: (errors: Record<string, string>) => void;
        onCancel?: () => void;
        onFinish?: () => void;
      }
    ) => void;
    reload: (options?: { only?: string[]; data?: Record<string, any>; [key: string]: any }) => void;
    get: (url: string, data?: Record<string, any>, options?: Record<string, any>) => void;
    post: (url: string, data?: Record<string, any>, options?: Record<string, any>) => void;
    put: (url: string, data?: Record<string, any>, options?: Record<string, any>) => void;
    patch: (url: string, data?: Record<string, any>, options?: Record<string, any>) => void;
    delete: (url: string, options?: Record<string, any>) => void;
  };
}
