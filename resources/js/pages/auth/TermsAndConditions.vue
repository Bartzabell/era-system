<script setup lang="ts">
import { ref, computed } from 'vue';

export interface TermsSection {
    title: string;
    content: string;
}

const props = defineProps<{
    privacyIntro: string;
    privacySections: TermsSection[];
    privacyCheckboxLabel: string;
    termsIntro: string;
    termsSections: TermsSection[];
    termsCheckboxLabel: string;
}>();

const emit = defineEmits<{
    close: []
    accept: []
}>();

const privacyChecked = ref(false);
const termsChecked = ref(false);

const allChecked = computed(() => privacyChecked.value && termsChecked.value);
</script>

<template>
    <div class="flex flex-col h-full">
        <!-- Header -->
        <div class="sticky top-0 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-6 py-4 flex items-center justify-between z-10">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-orange-100 flex items-center justify-center">
                    <svg class="w-4 h-4 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Terms and Conditions</h2>
            </div>
            <button
                @click="emit('close')"
                class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors rounded-full p-1 hover:bg-gray-100 dark:hover:bg-gray-700"
            >
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Scrollable Body -->
        <div class="flex-1 overflow-y-auto px-6 py-5 space-y-5 text-sm text-gray-700 dark:text-gray-300 leading-relaxed">

            <!-- Privacy Policy -->
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Privacy Policy</h2>

            <!-- eslint-disable-next-line vue/no-v-html -->
            <p v-html="privacyIntro"></p>

            <section v-for="(section, i) in privacySections" :key="'p-' + i">
                <h3 class="font-semibold text-gray-900 dark:text-white mb-1">{{ section.title }}</h3>
                <p>{{ section.content }}</p>
            </section>

            <!-- Privacy Checkbox -->
            <div class="pt-2">
                <label class="flex items-start gap-3 cursor-pointer">
                    <input
                        type="checkbox"
                        v-model="privacyChecked"
                        class="mt-0.5 w-4 h-4 text-orange-600 bg-gray-100 border-gray-300 rounded focus:ring-orange-500 dark:focus:ring-orange-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
                    />
                    <!-- eslint-disable-next-line vue/no-v-html -->
                    <span class="text-sm text-gray-700 dark:text-gray-300" v-html="privacyCheckboxLabel"></span>
                </label>
            </div>

            <!-- Terms of Use -->
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white pt-2">Terms of Use</h2>

            <!-- eslint-disable-next-line vue/no-v-html -->
            <p v-html="termsIntro"></p>

            <section v-for="(section, i) in termsSections" :key="'t-' + i">
                <h3 class="font-semibold text-gray-900 dark:text-white mb-1">{{ section.title }}</h3>
                <p>{{ section.content }}</p>
            </section>

            <!-- Terms Checkbox -->
            <div class="pt-2 pb-4">
                <label class="flex items-start gap-3 cursor-pointer">
                    <input
                        type="checkbox"
                        v-model="termsChecked"
                        class="mt-0.5 w-4 h-4 text-orange-600 bg-gray-100 border-gray-300 rounded focus:ring-orange-500 dark:focus:ring-orange-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
                    />
                    <!-- eslint-disable-next-line vue/no-v-html -->
                    <span class="text-sm text-gray-700 dark:text-gray-300" v-html="termsCheckboxLabel"></span>
                </label>
            </div>

        </div>

        <!-- Footer -->
        <div class="sticky bottom-0 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 px-6 py-4 flex justify-end">
            <button
                @click="emit('accept')"
                :disabled="!allChecked"
                :class="[
                    'px-5 py-2 rounded-full text-sm font-medium transition-colors',
                    allChecked
                        ? 'bg-orange-600 hover:bg-orange-700 text-white cursor-pointer'
                        : 'bg-gray-300 dark:bg-gray-600 text-gray-500 dark:text-gray-400 cursor-not-allowed'
                ]"
            >
                I Understand
            </button>
        </div>
    </div>
</template>