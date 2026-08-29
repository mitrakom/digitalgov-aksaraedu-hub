<script setup lang="ts">
// Props / Attributes like class and style are automatically inherited onto the root div
</script>

<template>
  <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
    <slot />
  </div>
</template>
