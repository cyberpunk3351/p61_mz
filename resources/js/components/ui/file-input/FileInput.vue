<script setup lang="ts" name="FileInput">
import type { HTMLAttributes } from 'vue'
import { cn } from '@/lib/utils'
import { computed, ref } from 'vue'

const props = defineProps<{
  accept?: string
  multiple?: boolean
  disabled?: boolean
  class?: HTMLAttributes['class']
}>()

const emits = defineEmits<{
  (e: 'change', files: FileList | null): void
}>()

const inputRef = ref<HTMLInputElement>()

const handleChange = (event: Event) => {
  const target = event.target as HTMLInputElement
  emits('change', target.files)
}

const openFileDialog = () => {
  inputRef.value?.click()
}

defineExpose({
  openFileDialog,
})
</script>

<template>
  <div
    :class="cn(
      'group relative flex h-9 w-full cursor-pointer items-center rounded-md border border-input bg-transparent text-sm shadow-xs transition-colors hover:bg-accent hover:text-accent-foreground focus-within:border-ring focus-within:ring-ring/50 focus-within:ring-[3px] disabled:pointer-events-none disabled:cursor-not-allowed disabled:opacity-50',
      props.class,
    )"
    @click="openFileDialog"
  >
    <input
      ref="inputRef"
      type="file"
      :accept="accept"
      :multiple="multiple"
      :disabled="disabled"
      class="absolute inset-0 h-full w-full cursor-pointer opacity-0"
      @change="handleChange"
    >
    <div class="flex flex-1 items-center px-3 py-1">
      <slot>
        <span class="text-muted-foreground">Choose file...</span>
      </slot>
    </div>
  </div>
</template>