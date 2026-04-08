<script setup lang="ts">
import { ref, computed } from 'vue';

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

            <!-- <p class="text-xs text-gray-400 uppercase tracking-wide font-semibold">Last updated: January 1, 2025</p> -->
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Privacy Policy</h2>
            <p>
                Please read our privacy policy before using <strong class="text-gray-900 dark:text-white">GEARS</strong> (General Emergency Alert and Response System).
                 Your data is used solely for emergency response purposes.
            </p>

            <section>
                <h3 class="font-semibold text-gray-900 dark:text-white mb-1">1. Data we collect</h3>
                <p>
                    GEARS collects your name, contact number, home address within General Mariano Alvarez (GMA), Cavite, and GPS location at the time of emergency reporting.
                    We also collect photos or videos you submit as part of an emergency report.
                </p>
            </section>

            <section>
                <h3 class="font-semibold text-gray-900 dark:text-white mb-1">2. How we use your data</h3>
                <p>
                   Your personal information is used exclusively to facilitate emergency dispatch and response by the GMA Cavite MDRRMO.
                    Your location data is shared in real-time with authorized dispatchers only during an active report.
                </p>
                <!-- <ul class="list-disc list-inside mt-2 space-y-1 pl-2">
                    <li>Use the platform only for lawful emergency response purposes.</li>
                    <li>Not misuse or falsify emergency reports or incident data.</li>
                    <li>Keep your account credentials confidential and secure.</li>
                    <li>Notify administrators immediately of any unauthorized access.</li>
                </ul> -->
            </section>

            <section>
                <h3 class="font-semibold text-gray-900 dark:text-white mb-1">3. Data sharing</h3>
                <p>
                    We do not sell, trade, or share your personal data with third parties.
                    Data may be shared with relevant government agencies (BFP, PNP, NDRRMC) when required by law or for official emergency coordination purposes.
                </p>
            </section>

            <section>
                <h3 class="font-semibold text-gray-900 dark:text-white mb-1">4. Data retention</h3>
                <p>
                    Emergency report data is retained for a minimum of 5 years in compliance with local government records retention policies.
                    You may request access to your personal data by contacting the GMA MDRRMO office.
                </p>
            </section>

            <section>
                <h3 class="font-semibold text-gray-900 dark:text-white mb-1">5. Your rights</h3>
                <p>
                    Under the Data Privacy Act of 2012 (RA 10173), you have the right to access, correct, and object to the processing of your personal data.
                     Contact the GMA MDRRMO Data Privacy Officer for any concerns.
                </p>
            </section>

            <!-- Privacy Policy Checkbox -->
            <div class="pt-2">
                <label class="flex items-start gap-3 cursor-pointer">
                    <input
                        type="checkbox"
                        v-model="privacyChecked"
                        class="mt-0.5 w-4 h-4 text-orange-600 bg-gray-100 border-gray-300 rounded focus:ring-orange-500 dark:focus:ring-orange-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
                    />
                    <span class="text-sm text-gray-700 dark:text-gray-300">
                        I have read and agree to the <strong class="text-gray-900 dark:text-white">Privacy Policy</strong> of GEARS — GMA Cavite Emergency Response System.
                    </span>
                </label>
            </div>

            <h2 class="text-lg font-semibold text-gray-900 dark:text-white pt-2">Terms of Use</h2>

            <p>
                By using <strong class="text-gray-900 dark:text-white">GEARS</strong>,
                you agree to use this app solely for genuine emergencies within General Mariano Alvarez (GMA), Cavite.
            </p>

            <section>
                <h3 class="font-semibold text-gray-900 dark:text-white mb-1">1. Eligibility</h3>
                <p>
                    GEARS is available to all registered residents of General Mariano Alvarez (GMA), Cavite.
                     Users must be 18 years of age or older to register independently.
                     Users below 18 years old are not permitted to create their own account and must be registered under the account of a parent or guardian who is a resident of GMA, Cavite and is 18 years of age or older.
                </p>
            </section>

            <section>
                <h3 class="font-semibold text-gray-900 dark:text-white mb-1">2. Age requirement</h3>
                <p>
                   By registering, you confirm that you are at least 18 years old.
                   If you are below 18, your parent or legal guardian must complete the registration on your behalf using their own personal information and contact number.
                   The account will be held in the name of the parent or guardian, who will be fully responsible for all activity and reports submitted through it.

                </p>

            </section>

            <section>
                <h3 class="font-semibold text-gray-900 dark:text-white mb-1">3. Residency requirement</h3>
                <p>
                   GEARS is exclusively for residents of General Mariano Alvarez (GMA), Cavite.
                   You confirm that you currently reside within GMA at the time of registration. The MDRRMO reserves the right to verify your identity, age, and address before activating your account.
                    Non-residents are not eligible to register.
                </p>
            </section>

            <section>
                <h3 class="font-semibold text-gray-900 dark:text-white mb-1">4. Proper Use</h3>
                <p>
                    This app is strictly for reporting genuine emergencies occurring within or near GMA, Cavite.
                     Filing false, misleading, or prank emergency reports is a serious violation of this agreement and may result in account suspension and legal action under the Revised Penal Code and applicable local ordinances of GMA, Cavite.
                </p>
            </section>

            <section>
                <h3 class="font-semibold text-gray-900 dark:text-white mb-1">5. Account responsibility</h3>
                <p>
                    You are solely responsible for all activity conducted under your registered account. Do not share your login credentials, OTP codes, or registered phone number with unauthorized individuals.
                     If the account is registered on behalf of a minor, the parent or guardian account holder is fully and legally responsible for all reports and actions submitted through the account.
                </p>
            </section>

            <section>
                <h3 class="font-semibold text-gray-900 dark:text-white mb-1">6. Parental and guardian consent</h3>
                <p>
                   If you are registering on behalf of a minor (below 18 years old), you confirm that you are the legal parent or guardian of that minor, that you reside in GMA Cavite, and that you give your full and informed consent for the minor to use GEARS under your registered account and supervision.
                </p>
            </section>

            <section>
                <h3 class="font-semibold text-gray-900 dark:text-white mb-1">7. Limitation of liability</h3>
                <p>
                   GEARS and the GMA Cavite MDRRMO are not liable for delays in emergency response caused by inaccurate location data, poor network connection, device failure, or force majeure events beyond the control of the system or its operators.
                </p>
            </section>

            <!-- Terms of Use Checkbox -->
            <div class="pt-2 pb-4">
                <label class="flex items-start gap-3 cursor-pointer">
                    <input
                        type="checkbox"
                        v-model="termsChecked"
                        class="mt-0.5 w-4 h-4 text-orange-600 bg-gray-100 border-gray-300 rounded focus:ring-orange-500 dark:focus:ring-orange-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
                    />
                    <span class="text-sm text-gray-700 dark:text-gray-300">
                        I agree to the <strong class="text-gray-900 dark:text-white">Terms of Use</strong> of GEARS and confirm that I meet the age and residency requirements, and that I will only use this app to report genuine emergencies in GMA, Cavite.
                    </span>
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
