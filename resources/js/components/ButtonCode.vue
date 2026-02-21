<script setup>
defineProps({
    text: {
        type: String,
        required: false,
    },

    icon: {
        type: Object, // Accepts a Vue component as an icon
        default: null, // Optional
    },

    color: {
        type: String,
        default: "bg-teal-500 hover:bg-teal-700",
    },

    type: {
        type: String,
        default: "button",
    }
});
</script>

<template>
    <button
        :type="type"
        :class="[color, 'px-4 py-1 text-white flex items-center gap-2 font-bold rounded-lg']"
    >
        <!-- Render icon if provided -->
        <component v-if="icon" :is="icon" class="w-5 h-5" />
        {{ text }}
    </button>
</template>
