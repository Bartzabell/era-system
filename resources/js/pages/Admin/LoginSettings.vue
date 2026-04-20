<script setup lang="ts">
import { ref, reactive } from 'vue';
import { useForm, Head, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';

interface Section {
    title: string;
    content: string;
}

interface FeatureCard {
    icon: string;
    title: string;
    description: string;
}

const props = defineProps<{
    loginSettings: Record<string, string>;
    termsSettings: Record<string, string>;
}>();

// ─── Parse JSON fields ───────────────────────────────────────────────────────
function parseJson<T>(raw: string | undefined, fallback: T): T {
    try { return raw ? JSON.parse(raw) : fallback; } catch { return fallback; }
}

const iconOptions = ['PhPhone', 'PhBell', 'PhMapPin', 'PhClipboardText', 'PhFirstAid', 'PhShield', 'PhHospital', 'PhSiren'];

// ─── Login form ──────────────────────────────────────────────────────────────
const loginForm = useForm({
    auth_title:       props.loginSettings.auth_title       ?? '',
    auth_description: props.loginSettings.auth_description ?? '',
    stat_1_value:     props.loginSettings.stat_1_value     ?? '',
    stat_1_label:     props.loginSettings.stat_1_label     ?? '',
    stat_2_value:     props.loginSettings.stat_2_value     ?? '',
    stat_2_label:     props.loginSettings.stat_2_label     ?? '',
    stat_3_value:     props.loginSettings.stat_3_value     ?? '',
    stat_3_label:     props.loginSettings.stat_3_label     ?? '',
    stat_4_value:     props.loginSettings.stat_4_value     ?? '',
    stat_4_label:     props.loginSettings.stat_4_label     ?? '',
    hero_title:       props.loginSettings.hero_title       ?? '',
    hero_subtitle:    props.loginSettings.hero_subtitle    ?? '',
    hero_footer:      props.loginSettings.hero_footer      ?? '',
    feature_cards:    parseJson<FeatureCard[]>(props.loginSettings.feature_cards, []) as FeatureCard[],
});

function addCard() {
    loginForm.feature_cards.push({ icon: 'PhPhone', title: '', description: '' });
}
function removeCard(i: number) {
    loginForm.feature_cards.splice(i, 1);
}

function submitLogin() {
    loginForm.put(('/login-settings/login'));
}

// ─── Terms form ──────────────────────────────────────────────────────────────
const termsForm = useForm({
    privacy_intro:          props.termsSettings.privacy_intro          ?? '',
    privacy_sections:       parseJson<Section[]>(props.termsSettings.privacy_sections, []) as Section[],
    privacy_checkbox_label: props.termsSettings.privacy_checkbox_label ?? '',
    terms_intro:            props.termsSettings.terms_intro            ?? '',
    terms_sections:         parseJson<Section[]>(props.termsSettings.terms_sections, []) as Section[],
    terms_checkbox_label:   props.termsSettings.terms_checkbox_label   ?? '',
});

function addPrivacySection() {
    termsForm.privacy_sections.push({ title: '', content: '' });
}
function removePrivacySection(i: number) {
    termsForm.privacy_sections.splice(i, 1);
}
function addTermsSection() {
    termsForm.terms_sections.push({ title: '', content: '' });
}
function removeTermsSection(i: number) {
    termsForm.terms_sections.splice(i, 1);
}

function submitTerms() {
    termsForm.put(('/login-settings/terms'));
}

// Active tab
const activeTab = ref<'login' | 'terms'>('login');
</script>

<template>
    <AppLayout>
        <Head title="Login Page Settings" />

        <div class="max-w-4xl mx-auto px-4 py-8">
            <!-- Page header -->
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Login Page Settings</h1>
                <p class="text-sm text-gray-500 mt-1">Customize what is displayed on the public login page and the Terms & Conditions modal.</p>
            </div>

            <!-- Tabs -->
            <div class="flex gap-1 bg-gray-100 dark:bg-gray-800 p-1 rounded-lg w-fit mb-6">
                <button
                    @click="activeTab = 'login'"
                    :class="[
                        'px-4 py-1.5 text-sm font-medium rounded-md transition-colors',
                        activeTab === 'login'
                            ? 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm'
                            : 'text-gray-500 hover:text-gray-700 dark:hover:text-gray-300'
                    ]"
                >
                    Login Page
                </button>
                <button
                    @click="activeTab = 'terms'"
                    :class="[
                        'px-4 py-1.5 text-sm font-medium rounded-md transition-colors',
                        activeTab === 'terms'
                            ? 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm'
                            : 'text-gray-500 hover:text-gray-700 dark:hover:text-gray-300'
                    ]"
                >
                    Terms & Conditions
                </button>
            </div>

            <!-- ════════════════ LOGIN TAB ════════════════ -->
            <form v-if="activeTab === 'login'" @submit.prevent="submitLogin" class="space-y-8">

                <!-- Success flash -->
                <div v-if="loginForm.recentlySuccessful" class="bg-green-50 border border-green-200 text-green-700 text-sm rounded-lg px-4 py-3">
                    Login page settings saved successfully.
                </div>

                <!-- Auth Box Section -->
                <section class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 space-y-4">
                    <h2 class="font-semibold text-gray-900 dark:text-white text-base">Auth Box (left panel)</h2>

                    <div class="grid gap-2">
                        <Label>Title</Label>
                        <Input v-model="loginForm.auth_title" placeholder="Welcome Back!" />
                        <p v-if="loginForm.errors.auth_title" class="text-xs text-red-500">{{ loginForm.errors.auth_title }}</p>
                    </div>
                    <div class="grid gap-2">
                        <Label>Description</Label>
                        <Input v-model="loginForm.auth_description" placeholder="Sign in to your account to continue" />
                        <p v-if="loginForm.errors.auth_description" class="text-xs text-red-500">{{ loginForm.errors.auth_description }}</p>
                    </div>

                    <h3 class="font-medium text-gray-700 dark:text-gray-300 text-sm pt-2">Stats Grid (4 tiles)</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div v-for="n in 4" :key="n" class="space-y-2 bg-gray-50 dark:bg-gray-700/40 rounded-lg p-3">
                            <p class="text-xs font-semibold text-gray-500 uppercase">Stat {{ n }}</p>
                            <div class="grid gap-1">
                                <Label class="text-xs">Value</Label>
                                <Input v-model="(loginForm as any)[`stat_${n}_value`]" placeholder="e.g. 24/7" />
                            </div>
                            <div class="grid gap-1">
                                <Label class="text-xs">Label</Label>
                                <Input v-model="(loginForm as any)[`stat_${n}_label`]" placeholder="e.g. Always on" />
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Hero Section -->
                <section class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 space-y-4">
                    <h2 class="font-semibold text-gray-900 dark:text-white text-base">Hero Panel (right panel)</h2>

                    <div class="grid gap-2">
                        <Label>Title <span class="text-gray-400 font-normal text-xs">(use \n for line break)</span></Label>
                        <textarea
                            v-model="loginForm.hero_title"
                            rows="2"
                            class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500"
                            placeholder="Reliable Response,&#10;Reliable Protection"
                        ></textarea>
                        <p v-if="loginForm.errors.hero_title" class="text-xs text-red-500">{{ loginForm.errors.hero_title }}</p>
                    </div>

                    <div class="grid gap-2">
                        <Label>Subtitle</Label>
                        <textarea
                            v-model="loginForm.hero_subtitle"
                            rows="3"
                            class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500"
                        ></textarea>
                    </div>

                    <div class="grid gap-2">
                        <Label>Footer text</Label>
                        <Input v-model="loginForm.hero_footer" placeholder="Powered by GMA Cavite MDRRMO..." />
                    </div>
                </section>

                <!-- Feature Cards -->
                <section class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 space-y-4">
                    <div class="flex items-center justify-between">
                        <h2 class="font-semibold text-gray-900 dark:text-white text-base">Feature Cards</h2>
                        <Button type="button" variant="outline" size="sm" @click="addCard">+ Add Card</Button>
                    </div>

                    <div v-if="loginForm.feature_cards.length === 0" class="text-sm text-gray-400 text-center py-4">
                        No feature cards. Click "Add Card" to add one.
                    </div>

                    <div class="space-y-4">
                        <div
                            v-for="(card, i) in loginForm.feature_cards"
                            :key="i"
                            class="bg-gray-50 dark:bg-gray-700/40 rounded-lg p-4 space-y-3"
                        >
                            <div class="flex items-center justify-between">
                                <p class="text-xs font-semibold text-gray-500 uppercase">Card {{ i + 1 }}</p>
                                <button
                                    type="button"
                                    @click="removeCard(i)"
                                    class="text-xs text-red-500 hover:text-red-700"
                                >Remove</button>
                            </div>
                            <div class="grid gap-1">
                                <Label class="text-xs">Icon</Label>
                                <select
                                    v-model="card.icon"
                                    class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500"
                                >
                                    <option v-for="icon in iconOptions" :key="icon" :value="icon">{{ icon }}</option>
                                </select>
                            </div>
                            <div class="grid gap-1">
                                <Label class="text-xs">Title</Label>
                                <Input v-model="card.title" placeholder="Card title" />
                            </div>
                            <div class="grid gap-1">
                                <Label class="text-xs">Description</Label>
                                <textarea
                                    v-model="card.description"
                                    rows="2"
                                    class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500"
                                ></textarea>
                            </div>
                        </div>
                    </div>
                </section>

                <div class="flex justify-end">
                    <Button
                        type="submit"
                        :disabled="loginForm.processing"
                        class="bg-orange-600 hover:bg-orange-700 text-white rounded-full px-6"
                    >
                        {{ loginForm.processing ? 'Saving...' : 'Save Login Settings' }}
                    </Button>
                </div>
            </form>

            <!-- ════════════════ TERMS TAB ════════════════ -->
            <form v-if="activeTab === 'terms'" @submit.prevent="submitTerms" class="space-y-8">

                <div v-if="termsForm.recentlySuccessful" class="bg-green-50 border border-green-200 text-green-700 text-sm rounded-lg px-4 py-3">
                    Terms & Conditions saved successfully.
                </div>

                <!-- Privacy Policy -->
                <section class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 space-y-4">
                    <h2 class="font-semibold text-gray-900 dark:text-white text-base">Privacy Policy</h2>

                    <div class="grid gap-2">
                        <Label>Intro paragraph <span class="text-gray-400 font-normal text-xs">(HTML allowed: &lt;strong&gt;, &lt;em&gt;, etc.)</span></Label>
                        <textarea
                            v-model="termsForm.privacy_intro"
                            rows="3"
                            class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500"
                        ></textarea>
                    </div>

                    <div class="flex items-center justify-between pt-2">
                        <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300">Sections</h3>
                        <Button type="button" variant="outline" size="sm" @click="addPrivacySection">+ Add Section</Button>
                    </div>

                    <div class="space-y-3">
                        <div
                            v-for="(section, i) in termsForm.privacy_sections"
                            :key="i"
                            class="bg-gray-50 dark:bg-gray-700/40 rounded-lg p-4 space-y-2"
                        >
                            <div class="flex items-center justify-between">
                                <p class="text-xs font-semibold text-gray-500 uppercase">Section {{ i + 1 }}</p>
                                <button type="button" @click="removePrivacySection(i)" class="text-xs text-red-500 hover:text-red-700">Remove</button>
                            </div>
                            <div class="grid gap-1">
                                <Label class="text-xs">Section heading</Label>
                                <Input v-model="section.title" placeholder="1. Data we collect" />
                            </div>
                            <div class="grid gap-1">
                                <Label class="text-xs">Content</Label>
                                <textarea
                                    v-model="section.content"
                                    rows="3"
                                    class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500"
                                ></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="grid gap-2 pt-2">
                        <Label>Privacy checkbox label <span class="text-gray-400 font-normal text-xs">(HTML allowed)</span></Label>
                        <textarea
                            v-model="termsForm.privacy_checkbox_label"
                            rows="2"
                            class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500"
                        ></textarea>
                    </div>
                </section>

                <!-- Terms of Use -->
                <section class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 space-y-4">
                    <h2 class="font-semibold text-gray-900 dark:text-white text-base">Terms of Use</h2>

                    <div class="grid gap-2">
                        <Label>Intro paragraph <span class="text-gray-400 font-normal text-xs">(HTML allowed)</span></Label>
                        <textarea
                            v-model="termsForm.terms_intro"
                            rows="3"
                            class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500"
                        ></textarea>
                    </div>

                    <div class="flex items-center justify-between pt-2">
                        <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300">Sections</h3>
                        <Button type="button" variant="outline" size="sm" @click="addTermsSection">+ Add Section</Button>
                    </div>

                    <div class="space-y-3">
                        <div
                            v-for="(section, i) in termsForm.terms_sections"
                            :key="i"
                            class="bg-gray-50 dark:bg-gray-700/40 rounded-lg p-4 space-y-2"
                        >
                            <div class="flex items-center justify-between">
                                <p class="text-xs font-semibold text-gray-500 uppercase">Section {{ i + 1 }}</p>
                                <button type="button" @click="removeTermsSection(i)" class="text-xs text-red-500 hover:text-red-700">Remove</button>
                            </div>
                            <div class="grid gap-1">
                                <Label class="text-xs">Section heading</Label>
                                <Input v-model="section.title" placeholder="1. Eligibility" />
                            </div>
                            <div class="grid gap-1">
                                <Label class="text-xs">Content</Label>
                                <textarea
                                    v-model="section.content"
                                    rows="3"
                                    class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500"
                                ></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="grid gap-2 pt-2">
                        <Label>Terms checkbox label <span class="text-gray-400 font-normal text-xs">(HTML allowed)</span></Label>
                        <textarea
                            v-model="termsForm.terms_checkbox_label"
                            rows="2"
                            class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500"
                        ></textarea>
                    </div>
                </section>

                <div class="flex justify-end">
                    <Button
                        type="submit"
                        :disabled="termsForm.processing"
                        class="bg-orange-600 hover:bg-orange-700 text-white rounded-full px-6"
                    >
                        {{ termsForm.processing ? 'Saving...' : 'Save Terms & Conditions' }}
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
