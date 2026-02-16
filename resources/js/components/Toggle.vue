<script setup lang="ts">
import { computed } from 'vue';

interface Props {
    modelValue: boolean;
    label?: string;
    description?: string;
    disabled?: boolean;
    size?: 'sm' | 'md' | 'lg';
}

const props = withDefaults(defineProps<Props>(), {
    modelValue: false,
    disabled: false,
    size: 'md',
});

const emit = defineEmits<{
    'update:modelValue': [value: boolean];
}>();

const toggle = () => {
    if (!props.disabled) {
        emit('update:modelValue', !props.modelValue);
    }
};

const sizeClasses = computed(() => {
    switch (props.size) {
        case 'sm':
            return {
                container: 'w-9 h-5',
                thumb: 'w-4 h-4',
                translate: 'translate-x-4',
            };
        case 'lg':
            return {
                container: 'w-14 h-7',
                thumb: 'w-6 h-6',
                translate: 'translate-x-7',
            };
        default: // md
            return {
                container: 'w-11 h-6',
                thumb: 'w-5 h-5',
                translate: 'translate-x-5',
            };
    }
});
</script>

<template>
    <div class="flex items-center gap-3">
        <!-- Toggle Switch -->
        <button
            type="button"
            role="switch"
            :aria-checked="modelValue"
            :disabled="disabled"
            @click="toggle"
            :class="[
                'relative inline-flex items-center rounded-full transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2',
                sizeClasses.container,
                modelValue
                    ? 'bg-orange-600'
                    : 'bg-gray-200',
                disabled
                    ? 'opacity-50 cursor-not-allowed'
                    : 'cursor-pointer hover:shadow-md',
            ]"
        >
            <span
                :class="[
                    'inline-block transform rounded-full bg-white shadow-lg transition-transform duration-200 ease-in-out',
                    sizeClasses.thumb,
                    modelValue
                        ? sizeClasses.translate
                        : 'translate-x-0.5',
                ]"
            />
        </button>

        <!-- Label and Description -->
        <div v-if="label || description" class="flex flex-col">
            <label
                v-if="label"
                :class="[
                    'text-sm font-medium select-none',
                    disabled ? 'text-gray-400' : 'text-gray-700 cursor-pointer',
                ]"
                @click="toggle"
            >
                {{ label }}
            </label>
            <span
                v-if="description"
                :class="[
                    'text-xs',
                    disabled ? 'text-gray-300' : 'text-gray-500',
                ]"
            >
                {{ description }}
            </span>
        </div>
    </div>
</template>
