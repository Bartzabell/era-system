<script setup lang="ts">
import { computed, ref } from 'vue';

interface Props {
    modelValue?: string | number | null;
    type?: 'text' | 'number' | 'email' | 'currency' | 'date' | 'datetime' | 'datetime-local' | 'password' | 'tel' | 'url';
    placeholder?: string;
    label?: string;
    error?: string;
    disabled?: boolean;
    readonly?: boolean;
    required?: boolean;
    min?: number | string;
    max?: number | string;
    step?: number | string;
    maxlength?: number;
    autocomplete?: string;
}

const props = withDefaults(defineProps<Props>(), {
    type: 'text',
    placeholder: '',
    disabled: false,
    readonly: false,
    required: false,
});

const emit = defineEmits<{
    'update:modelValue': [value: string | number | null];
    'blur': [event: FocusEvent];
    'focus': [event: FocusEvent];
}>();

const input = ref<HTMLInputElement>();

// Determine the actual input type based on prop
const inputType = computed(() => {
    if (props.type === 'currency') return 'text';
    if (props.type === 'datetime') return 'datetime-local';
    return props.type;
});

// Format currency display
const displayValue = computed({
    get: () => {
        if (props.type === 'currency' && props.modelValue) {
            const num = parseFloat(String(props.modelValue).replace(/[^0-9.-]/g, ''));
            if (!isNaN(num)) {
                return new Intl.NumberFormat('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2,
                }).format(num);
            }
        }
        return props.modelValue ?? '';
    },
    set: (value: string | number) => {
        if (props.type === 'currency') {
            const cleaned = String(value).replace(/[^0-9.-]/g, '');
            emit('update:modelValue', cleaned || null);
        } else if (props.type === 'number') {
            const num = value === '' ? null : Number(value);
            emit('update:modelValue', num);
        } else {
            emit('update:modelValue', value || null);
        }
    },
});

// Focus the input
const focus = () => {
    input.value?.focus();
};

// Expose focus method
defineExpose({ focus });
</script>

<template>
    <div class="w-full">
        <label v-if="label" class="block text-base font-medium text-gray-700 mb-1">
            {{ label }}
            <span v-if="required" class="text-red-500">*</span>
        </label>

        <div class="relative">
            <span
                v-if="type === 'currency'"
                class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-base"
            >
                ₱
            </span>

            <input
                ref="input"
                v-model="displayValue"
                :type="inputType"
                :placeholder="placeholder"
                :disabled="disabled"
                :readonly="readonly"
                :required="required"
                :min="min"
                :max="max"
                :step="step"
                :maxlength="maxlength"
                :autocomplete="autocomplete"
                :class="[
                    'w-full px-3 py-2 border rounded-md shadow-sm transition-colors',
                    'focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500',
                    'disabled:bg-gray-100 disabled:cursor-not-allowed disabled:text-gray-500',
                    'readonly:bg-gray-50 readonly:cursor-default',
                    error
                        ? 'border-red-500 focus:ring-red-500 focus:border-red-500'
                        : 'border-gray-300',
                    type === 'currency' ? 'pl-8' : '',
                ]"
                @blur="emit('blur', $event)"
                @focus="emit('focus', $event)"
            />
        </div>

        <p v-if="error" class="mt-1 text-base text-red-600">
            {{ error }}
        </p>
    </div>
</template>
