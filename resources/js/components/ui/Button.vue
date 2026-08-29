<script setup lang="ts">
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'

interface Props {
  variant?: 'primary' | 'secondary' | 'outline' | 'ghost' | 'danger' | 'success' | 'dark'
  size?: 'sm' | 'md' | 'lg' | 'icon'
  as?: 'button' | 'a' | 'link'
  href?: string
  disabled?: boolean
  type?: 'button' | 'submit' | 'reset'
  loading?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  variant: 'primary',
  size: 'md',
  as: 'button',
  type: 'button',
  disabled: false,
  loading: false,
})

const variantClasses = computed(() => {
  switch (props.variant) {
    case 'primary':
      return 'bg-emerald-600 hover:bg-emerald-700 text-white shadow-sm shadow-emerald-500/20 active:scale-[0.98]'
    case 'secondary':
      return 'bg-slate-100 hover:bg-slate-200 text-slate-900 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-slate-100'
    case 'outline':
      return 'border border-slate-300 hover:bg-slate-100/80 text-slate-700 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800'
    case 'ghost':
      return 'hover:bg-slate-100 text-slate-700 dark:text-slate-300 dark:hover:bg-slate-800/80'
    case 'danger':
      return 'bg-rose-600 hover:bg-rose-700 text-white shadow-sm shadow-rose-500/20 active:scale-[0.98]'
    case 'success':
      return 'bg-teal-600 hover:bg-teal-700 text-white shadow-sm shadow-teal-500/20 active:scale-[0.98]'
    case 'dark':
      return 'bg-slate-900 hover:bg-slate-800 text-white dark:bg-white dark:text-slate-900 dark:hover:bg-slate-100'
    default:
      return 'bg-emerald-600 hover:bg-emerald-700 text-white'
  }
})

const sizeClasses = computed(() => {
  switch (props.size) {
    case 'sm':
      return 'px-3 py-1.5 text-xs font-medium rounded-lg gap-1.5'
    case 'lg':
      return 'px-6 py-3 text-base font-semibold rounded-xl gap-2.5 shadow-md'
    case 'icon':
      return 'p-2 rounded-lg'
    default:
      return 'px-4 py-2 text-sm font-medium rounded-lg gap-2'
  }
})
</script>

<template>
  <Link
    v-if="as === 'link' && href"
    :href="href"
    class="inline-flex items-center justify-center transition-all duration-150 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 cursor-pointer disabled:opacity-50 disabled:pointer-events-none"
    :class="[variantClasses, sizeClasses]"
  >
    <slot />
  </Link>

  <a
    v-else-if="as === 'a' && href"
    :href="href"
    class="inline-flex items-center justify-center transition-all duration-150 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 cursor-pointer disabled:opacity-50 disabled:pointer-events-none"
    :class="[variantClasses, sizeClasses]"
  >
    <slot />
  </a>

  <button
    v-else
    :type="type"
    :disabled="disabled || loading"
    class="inline-flex items-center justify-center transition-all duration-150 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 cursor-pointer disabled:opacity-50 disabled:pointer-events-none"
    :class="[variantClasses, sizeClasses]"
  >
    <svg
      v-if="loading"
      class="animate-spin -ml-1 mr-2 h-4 w-4 text-current"
      fill="none"
      viewBox="0 0 24 24"
    >
      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
    </svg>
    <slot />
  </button>
</template>
