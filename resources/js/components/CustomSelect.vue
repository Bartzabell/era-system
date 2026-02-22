<script setup>
import { defineProps, defineEmits } from 'vue';

const props = defineProps({
  label: {
    type: String,
    required: false
  },
  name: {
    type: String,
    required: false
  },
  modelValue: {
    type: [String, Number],
    required: false
  },
  options: {
    type: Array,
    required: true
    // Each option should have a value and label property
    // [{value: 'production', label: 'Production'}, ...]
  },
  readonly: Boolean,
  disabled: Boolean,
  placeholder: {
    type: String,
    required: false,
    default: '-'
  }
})

const emit = defineEmits(['update:modelValue'])

const updateValue = (event) => {
  emit('update:modelValue', event.target.value)
}
</script>

<template>
  <div>
    <label class="text-[10px] 2xl:text-sm font-medium text-gray-600 rounded-xl">{{ label }}</label>
    <div
      class="flex w-full transition bg-white border border-black rounded-xl focus-within:ring-1 focus-within:ring-orange-900 ring-orange-100">
      <select :name="name" :value="modelValue" @input="updateValue" :readonly="readonly" :disabled="disabled" :class="[
        'w-full py-1.5 rounded-xl focus-within:ring-1 text-[10px] 2xl:text-base focus-within:ring-orange-900 ring-orange-100 focus:outline-none pl-2',
        { 'bg-gray-100': readonly || disabled }
      ]">
        <option value="" disabled selected hidden>{{ placeholder }}</option>
        <option v-for="option in options" :key="option.value" :value="option.value">
          {{ option.label }}
        </option>
      </select>
    </div>
  </div>
</template>
