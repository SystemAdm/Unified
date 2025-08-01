import { toast } from 'vue-sonner'

export const useToast = () => {
  return {
    toast,
    // Provide the same interface as ShadCN toast but using Sonner
    // This makes migration easier
    dismiss: (toastId?: string) => toast.dismiss(toastId),
    error: (message: string, options?: any) => toast.error(message, options),
    success: (message: string, options?: any) => toast.success(message, options),
    warning: (message: string, options?: any) => toast.warning(message, options),
    info: (message: string, options?: any) => toast.info(message, options),
  }
}

export type { ToastProps } from 'vue-sonner'
