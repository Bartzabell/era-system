<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { PhFirstAid } from '@phosphor-icons/vue'

defineOptions({
    inheritAttrs: false,
});

type Props = {
    className?: HTMLAttributes['class'];
};

defineProps<Props>();
</script>

<template>
    <div class="border border-black rounded-full">
        <PhFirstAid :size="32" weight="fill" class="text-red-500 bg-white rounded-full "/>
    </div>

</template>
