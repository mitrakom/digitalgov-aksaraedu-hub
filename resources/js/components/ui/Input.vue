<script setup lang="ts">
interface Props {
  modelValue?: string | number
  type?: string
  placeholder?: string
  disabled?: boolean
  error?: string
  id?: string
  required?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  type: 'text',
  placeholder: '',
  disabled: false,
  required: false,
})

const emit = defineEmits(['update:modelValue'])

const onInput = (event: Event) => {
  emit('update:modelValue', (event.target as HTMLInputElement).value)
}
</script>

<template>
  <div class="w-full">
    <input
      :id="id"
      :type="type"
      :value="modelValue"
      :placeholder="placeholder"
      :disabled="disabled"
      :required="required"
      @input="onInput"
      class="w-full px-3.5 py-2 text-sm rounded-lg border bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 placeholder:text-slate-400 dark:placeholder:text-slate-500 transition-colors focus:outline-none focus:ring-2 focus:ring-emerald-500/20 disabled:opacity-50 disabled:bg-slate-100 dark:disabled:bg-slate-800"
      :class="[
        error
          ? 'border-rose-500 focus:border-rose-500 focus:ring-rose-500/20'
          : 'border-slate-200 dark:border-slate-800 focus:border-emerald-500 dark:focus:border-emerald-500'
      ]"
    />
    <p v-if="error" class="mt-1.5 text-xs text-rose-500 font-medium">
      {{ error }}
    </p>
  </div>
</template>
