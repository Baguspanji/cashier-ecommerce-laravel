<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import type { SelectSeparatorProps } from "reka-ui"
import { SelectSeparator } from 'reka-ui'
import { cn } from '@/lib/utils'

interface Props extends SelectSeparatorProps {
  class?: HTMLAttributes['class']
}

const props = defineProps<Props>()
</script>

<template>
  <SelectSeparator
    data-slot="select-separator"
    v-bind="props"
    :class="cn('-mx-1 my-1 h-px bg-muted', props.class)"
  />
</template>
