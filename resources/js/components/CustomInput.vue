<script setup>
const model = defineModel({
    type: null,
    required: false,
});
const props = defineProps({
    name: { type: String, required: false },
    placeholder: { type: String, required: false },
    type: { type: String, default: "text" },
    input: { type: String, required: false },
    for: { type: String, required: false },
    message: String,
    readonly: Boolean,
    disabled: Boolean,
    dateStrict: { type: Boolean, default: false },
    dateRestrict: { type: [Number, String], default: 0 },
    noDecimal: { type: Boolean, default: false }
});

import { ref, onMounted, computed } from 'vue';

const inputRef = ref(null);
const currentDateTime = ref('');

// ── Date helper ───────────────────────────────────────────────────────────────

/**
 * Strips time portion so <input type="date"> can read it.
 * Handles: "2026-02-19 00:00:00", "2026-02-19T00:00:00", "2026-02-19"
 */
const normalizeDate = (val) => {
    if (!val) return '';
    if (props.type === 'datetime-local') {
        // Keep full datetime, just normalize the separator
        // Handles: "2026-02-19 14:30:00", "2026-02-19T14:30:00"
        const normalized = val.replace(' ', 'T');      // space → T
        return normalized.substring(0, 16);            // YYYY-MM-DDTHH:mm
    }
    return val.split('T')[0].split(' ')[0];            // date only
};

// ── Type resolution ───────────────────────────────────────────────────────────

const actualType = computed(() => {
    if (props.input === 'number') return 'text';
    return props.type; // keep 'date' / 'datetime-local' as-is
});

const isDateType = computed(() => props.type === 'date' || props.type === 'datetime-local');

// ── Display value ─────────────────────────────────────────────────────────────

const displayValue = computed({
    get() {
        if (isDateType.value) {
            return normalizeDate(model.value);
        }
        if (props.noDecimal && (props.type === 'number' || props.input === 'number')) {
            if (model.value !== null && model.value !== undefined && model.value !== '') {
                return parseInt(model.value).toString();
            }
        }
        return model.value;
    },
    set(value) {
        if (isDateType.value) {
            // value from <input type="date"> is always yyyy-mm-dd, pass directly
            model.value = value || '';
            return;
        }
        if (props.input === 'number') {
            if (value === '' || value === null || value === undefined) {
                model.value = null;
            } else {
                const numValue = props.noDecimal ? parseInt(value) : parseFloat(value);
                model.value = isNaN(numValue) ? null : numValue;
            }
        } else {
            model.value = value;
        }
    }
});

// ── Date restrictions ─────────────────────────────────────────────────────────

const dateRestrictions = computed(() => {
    if (!isDateType.value) return {};

    const restrictions = {};

    if (props.dateStrict) {
        restrictions.min = currentDateTime.value;
    }

    if (props.dateRestrict && Number(props.dateRestrict) > 0) {
        const date = new Date();
        date.setDate(date.getDate() + Number(props.dateRestrict));
        const y = date.getFullYear();
        const m = String(date.getMonth() + 1).padStart(2, '0');
        const d = String(date.getDate()).padStart(2, '0');
        const h = String(date.getHours()).padStart(2, '0');
        const min = String(date.getMinutes()).padStart(2, '0');
        restrictions.min = props.type === 'datetime-local'
            ? `${y}-${m}-${d}T${h}:${min}`
            : `${y}-${m}-${d}`;
    }

    return restrictions;
});

const inputAttributes = computed(() => ({ ...dateRestrictions.value }));

// ── Lifecycle ─────────────────────────────────────────────────────────────────

onMounted(() => {
    if (isDateType.value) {
        const now = new Date();
        const y = now.getFullYear();
        const m = String(now.getMonth() + 1).padStart(2, '0');
        const d = String(now.getDate()).padStart(2, '0');
        const h = String(now.getHours()).padStart(2, '0');
        const min = String(now.getMinutes()).padStart(2, '0');
        currentDateTime.value = props.type === 'datetime-local'
            ? `${y}-${m}-${d}T${h}:${min}`
            : `${y}-${m}-${d}`;
    }
});

// ── Input handler ─────────────────────────────────────────────────────────────

const handleInput = (event) => {
    if (isDateType.value) {
        // Browser date picker always yields yyyy-mm-dd, nothing to sanitize
        return;
    }

    if (props.for === 'contact') {
        event.target.value = event.target.value.replace(/[^0-9]/g, '');
    } else if (props.input === 'number') {
        if (props.noDecimal === true) {
            event.target.value = event.target.value.replace(/[^0-9]/g, '');
        } else {
            let newValue = event.target.value.replace(/[^0-9.]/g, '');
            const decimalCount = (newValue.match(/\./g) || []).length;
            if (decimalCount > 1) {
                const firstDecimalIndex = newValue.indexOf('.');
                newValue = newValue.slice(0, firstDecimalIndex + 1) +
                    newValue.slice(firstDecimalIndex + 1).replace(/\./g, '');
            }
            event.target.value = newValue;
        }
    }
};
</script>

<template>
    <div>
        <label class="text-[10px] 2xl:text-sm font-medium text-gray-600 dark:text-gray-200">{{ name }}</label>
        <div
            :class="{ '!border-red-500 !border-2': message }"
            class="flex rounded-xl transition bg-white border border-black dark:bg-gray-400 focus-within:ring-1 dark:focus-within:ring-sky-300 focus-within:ring-sky-600 ring-sky-100"
        >
            <input
                ref="inputRef"
                v-model="displayValue"
                :placeholder="placeholder"
                :readonly="readonly"
                :disabled="disabled"
                @input="handleInput"
                v-bind="inputAttributes"
                :class="[
                    'w-full py-[5px] rounded-xl focus-within:ring-1 dark:bg-gray-500 dark:text-gray-200 text-[10px] 2xl:text-base dark:focus-within:ring-sky-300 focus-within:ring-sky-900 ring-sky-100 focus:outline-none pl-2',
                    { 'text-gray-500 font-black': readonly || disabled }
                ]"
                :type="actualType"
            />
        </div>
        <small class="text-xs italic text-red-500 md:text-sm" v-if="message">{{ message }}</small>
    </div>
</template>
