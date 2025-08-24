// Type declarations for UI components
import * as React from 'react';

declare module '@/components/ui/textarea' {
  export interface TextareaProps extends React.TextareaHTMLAttributes<HTMLTextAreaElement> {}
  export const Textarea: React.ForwardRefExoticComponent<TextareaProps>;
}

declare module '@/components/ui/switch' {
  export interface SwitchProps extends React.ButtonHTMLAttributes<HTMLButtonElement> {
    checked?: boolean;
    onCheckedChange?: (checked: boolean) => void;
  }
  export const Switch: React.ForwardRefExoticComponent<SwitchProps>;
}

declare module '@/components/ui/tabs' {
  export interface TabsProps {
    defaultValue?: string;
    value?: string;
    onValueChange?: (value: string) => void;
    children: React.ReactNode;
  }
  export const Tabs: React.ForwardRefExoticComponent<TabsProps>;
  
  export interface TabsListProps extends React.HTMLAttributes<HTMLDivElement> {}
  export const TabsList: React.ForwardRefExoticComponent<TabsListProps>;
  
  export interface TabsTriggerProps extends React.ButtonHTMLAttributes<HTMLButtonElement> {
    value: string;
  }
  export const TabsTrigger: React.ForwardRefExoticComponent<TabsTriggerProps>;
  
  export interface TabsContentProps extends React.HTMLAttributes<HTMLDivElement> {
    value: string;
  }
  export const TabsContent: React.ForwardRefExoticComponent<TabsContentProps>;
}

declare module '@/components/ui/use-toast' {
  export interface ToastProps {
    title?: string;
    description?: React.ReactNode;
    action?: React.ReactNode;
    variant?: 'default' | 'destructive';
  }
  
  export interface ToastActionElement {
    altText: string;
    action: React.ReactNode;
  }
  
  export interface Toast extends ToastProps {
    id: string;
    open: boolean;
    onOpenChange: (open: boolean) => void;
  }
  
  export interface ToastOptions extends ToastProps {
    duration?: number;
  }
  
  export interface ToastContextValue {
    toast: (props: ToastOptions) => void;
    dismiss: (toastId?: string) => void;
  }
  
  export const useToast: () => {
    toast: (props: ToastOptions) => void;
    dismiss: (toastId?: string) => void;
    toasts: Toast[];
  };
}
