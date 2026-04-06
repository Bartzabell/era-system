<script setup lang="ts">
const emit = defineEmits<{
    close: []
    accept: []
}>();
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

            <p class="text-xs text-gray-400 uppercase tracking-wide font-semibold">Last updated: January 1, 2025</p>

            <p>
                Welcome to <strong class="text-gray-900 dark:text-white">GEARS</strong> (General Emergency Alert and Response System).
                By accessing or using our platform, you agree to be bound by these Terms and Conditions. Please read them carefully.
            </p>

            <section>
                <h3 class="font-semibold text-gray-900 dark:text-white mb-1">1. Acceptance of Terms</h3>
                <p>
                    By creating an account or logging into the GEARS platform, you acknowledge that you have read, understood, and agree
                    to be bound by these Terms and Conditions and our Privacy Policy. If you do not agree to these terms, you must not
                    use the platform.
                </p>
            </section>

            <section>
                <h3 class="font-semibold text-gray-900 dark:text-white mb-1">2. Use of the Platform</h3>
                <p>
                    The GEARS platform is intended for authorized users involved in emergency response coordination. You agree to:
                </p>
                <ul class="list-disc list-inside mt-2 space-y-1 pl-2">
                    <li>Use the platform only for lawful emergency response purposes.</li>
                    <li>Not misuse or falsify emergency reports or incident data.</li>
                    <li>Keep your account credentials confidential and secure.</li>
                    <li>Notify administrators immediately of any unauthorized access.</li>
                </ul>
            </section>

            <section>
                <h3 class="font-semibold text-gray-900 dark:text-white mb-1">3. Data Privacy</h3>
                <p>
                    We collect and process personal data in accordance with applicable data privacy laws. Your data may be used for
                    emergency coordination, reporting, and system improvement purposes. We do not sell or share your personal data
                    with third parties without your consent, except as required by law.
                </p>
            </section>

            <section>
                <h3 class="font-semibold text-gray-900 dark:text-white mb-1">4. Accuracy of Information</h3>
                <p>
                    You are responsible for ensuring that any incident reports, location data, or information you submit through the
                    platform is accurate and truthful. Submitting false or misleading emergency information may have legal consequences
                    and could endanger lives.
                </p>
            </section>

            <section>
                <h3 class="font-semibold text-gray-900 dark:text-white mb-1">5. Limitation of Liability</h3>
                <p>
                    GEARS and its administrators shall not be liable for any indirect, incidental, or consequential damages arising
                    from the use or inability to use the platform. We strive to maintain uptime but cannot guarantee uninterrupted
                    service during emergencies or technical outages.
                </p>
            </section>

            <section>
                <h3 class="font-semibold text-gray-900 dark:text-white mb-1">6. Account Termination</h3>
                <p>
                    We reserve the right to suspend or terminate accounts that violate these Terms and Conditions, misuse the platform,
                    or engage in behavior that compromises the integrity of emergency response operations.
                </p>
            </section>

            <section>
                <h3 class="font-semibold text-gray-900 dark:text-white mb-1">7. Changes to Terms</h3>
                <p>
                    We may update these Terms and Conditions from time to time. Continued use of the platform after updates constitutes
                    acceptance of the revised terms. We will notify users of significant changes via email or platform notification.
                </p>
            </section>

            <section>
                <h3 class="font-semibold text-gray-900 dark:text-white mb-1">8. Governing Law</h3>
                <p>
                    These Terms and Conditions are governed by the laws of the Republic of the Philippines. Any disputes arising from
                    the use of this platform shall be subject to the jurisdiction of Philippine courts.
                </p>
            </section>

        </div>

        <!-- Footer -->
        <div class="sticky bottom-0 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 px-6 py-4 flex justify-end">
            <button
                @click="emit('accept')"
                class="px-5 py-2 rounded-full bg-orange-600 hover:bg-orange-700 text-white text-sm font-medium transition-colors"
            >
                I Understand
            </button>
        </div>
    </div>
</template>
