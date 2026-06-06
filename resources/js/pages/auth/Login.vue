<script setup lang="ts">
import { ref } from 'vue';
import { Form, Head } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AuthBase from '@/layouts/AuthLayout.vue';
import Modal from '@/components/Modal.vue';
import TermsAndConditions from '@/pages/auth/TermsAndConditions.vue';
import { register } from '@/routes';
import { store } from '@/routes/login';
import { request } from '@/routes/password';
import { PhFirstAid, PhPhone, PhBell, PhMapPin, PhClipboardText } from '@phosphor-icons/vue';

// Map icon string names to actual components
const iconMap: Record<string, any> = {
    PhPhone,
    PhBell,
    PhMapPin,
    PhClipboardText,
    PhFirstAid,
};

export interface FeatureCard {
    icon: string;
    title: string;
    description: string;
}

const props = defineProps<{
    status?: string;
    canResetPassword: boolean;
    canRegister: boolean;

    // Auth box (left panel)
    authTitle: string;
    authDescription: string;
    stat1Value: string;
    stat1Label: string;
    stat2Value: string;
    stat2Label: string;
    stat3Value: string;
    stat3Label: string;
    stat4Value: string;
    stat4Label: string;

    // Hero (right panel)
    heroTitle: string;
    heroSubtitle: string;
    heroFooter: string;
    featureCards: FeatureCard[];

    // Terms content
    termsPrivacyIntro: string;
    termsPrivacySections: { title: string; content: string }[];
    termsPrivacyCheckbox: string;
    termsIntro: string;
    termsSections: { title: string; content: string }[];
    termsCheckbox: string;
}>();

const termsAccepted = ref(false);
const showTermsModal = ref(false);

// Split hero title on \n to preserve line breaks
const heroTitleLines = props.heroTitle.split('\n');
</script>

<template>
    <div class="flex bg-orange-400/75 h-screen overflow-hidden">
        <AuthBase
            class="bg-gray-400 w-fit h-screen p-10 flex justify-center"
            :title="authTitle"
            :description="authDescription"
        >
            <Head title="Log in" />

            <div
                v-if="status"
                class="mb-1 text-center text-sm font-medium text-green-600"
            >
                {{ status }}
            </div>

            <Form
                v-bind="store.form()"
                :reset-on-success="['password']"
                v-slot="{ errors, processing }"
                class="flex flex-col gap-6"
            >
                <div class="grid gap-4">
                    <div class="grid gap-2">
                        <Label for="email">Email Address</Label>
                        <Input
                            class="bg-gray-200 border-gray-200"
                            id="email"
                            name="email"
                            required
                            autofocus
                            type="email"
                            :tabindex="1"
                            autocomplete="email"
                            placeholder="email@sample"
                        />
                        <InputError :message="errors.email" />
                    </div>

                    <div class="grid gap-1">
                        <div class="flex items-center justify-between">
                            <Label for="password">Password</Label>
                            <TextLink
                                v-if="canResetPassword"
                                :href="request()"
                                class="text-sm"
                                :tabindex="5"
                            >
                                Forgot password?
                            </TextLink>
                        </div>
                        <Input
                            id="password"
                            type="password"
                            name="password"
                            required
                            :tabindex="2"
                            class="bg-gray-200 border-gray-200"
                            autocomplete="current-password"
                            placeholder="Password"
                        />
                        <InputError :message="errors.password" />
                    </div>

                    <div class="flex items-center justify-between">
                        <Label for="remember" class="flex items-center">
                            <Checkbox id="remember" name="remember" :tabindex="3" />
                            <span>Remember me</span>
                        </Label>
                    </div>

                    <!-- Terms and Conditions Checkbox -->
                    <div class="flex items-start space-x-1">
                        <Checkbox
                            id="terms"
                            :tabindex="4"
                            v-model="termsAccepted"
                            class="mt-0.5"
                        />
                        <Label for="terms" class="text-sm text-gray-600 leading-snug cursor-pointer select-none">
                            I have read and agree to the
                            <button
                                type="button"
                                @click="showTermsModal = true"
                                class="text-orange-600 underline underline-offset-2 hover:text-orange-700 font-medium transition-colors"
                            >
                                Terms and Conditions
                            </button>
                        </Label>
                    </div>

                    <Button
                        type="submit"
                        class="mt-2 w-full rounded-full bg-orange-600"
                        :tabindex="5"
                        :disabled="processing || !termsAccepted"
                        data-test="login-button"
                    >
                        <Spinner v-if="processing" />
                        Sign In
                    </Button>
                </div>

                <div class="text-center text-sm text-muted-foreground">
                    Don't have an account?
                    <TextLink
                        :href="register()"
                        :tabindex="6"
                        class="underline underline-offset-4 text-red-600"
                    >
                        Sign up
                    </TextLink>
                </div>
            </Form>

            <!-- Stats Grid -->
            <div class="grid grid-cols-2 gap-2 mt-1">
                <div class="bg-gray-300/60 rounded-lg p-3 text-center">
                    <div class="text-xl font-bold text-gray-900">{{ stat1Value }}</div>
                    <div class="text-xs text-gray-600">{{ stat1Label }}</div>
                </div>
                <div class="bg-gray-300/60 rounded-lg p-3 text-center">
                    <div class="text-xl font-bold text-gray-900">{{ stat2Value }}</div>
                    <div class="text-xs text-gray-600">{{ stat2Label }}</div>
                </div>
                <div class="bg-gray-300/60 rounded-lg p-3 text-center">
                    <div class="text-sm font-bold text-gray-900">{{ stat3Value }}</div>
                    <div class="text-xs text-gray-600">{{ stat3Label }}</div>
                </div>
                <div class="bg-gray-300/60 rounded-lg p-3 text-center">
                    <div class="text-sm font-bold text-gray-900">{{ stat4Value }}</div>
                    <div class="text-xs text-gray-600">{{ stat4Label }}</div>
                </div>
            </div>
        </AuthBase>

        <!-- Hero Panel -->
        <div class="flex flex-1 flex-col items-center justify-center text-center gap-4">
            <div >
                <img src="/storage/img/logo.png" class=" rounded-full w-[100px] h-[100px] object-contain" />
            </div>

            <h1 class="text-5xl text-white font-black">
                <template v-for="(line, i) in heroTitleLines" :key="i">
                    {{ line }}<br v-if="i < heroTitleLines.length - 1" />
                </template>
            </h1>

            <p class="text-white/90 max-w-xl whitespace-pre-line">{{ heroSubtitle }}</p>

            <!-- Feature Cards Grid -->
            <div class="grid grid-cols-2 gap-3 max-w-lg w-full mt-2">
                <div
                    v-for="(card, i) in featureCards"
                    :key="i"
                    class="bg-white/20 rounded-xl p-5 text-center"
                >
                    <component
                        :is="iconMap[card.icon] ?? PhFirstAid"
                        :size="28"
                        weight="fill"
                        class="text-white mx-auto mb-2"
                    />
                    <div class="text-sm font-bold text-white mb-1">{{ card.title }}</div>
                    <div class="text-xs text-white/85 leading-relaxed">{{ card.description }}</div>
                </div>
            </div>

            <div class="text-white font-black text-sm mt-10">{{ heroFooter }}</div>
        </div>

        <!-- Terms and Conditions Modal -->
        <Modal :show="showTermsModal" @close="showTermsModal = false" maxWidth="2xl">
            <TermsAndConditions
                :privacy-intro="termsPrivacyIntro"
                :privacy-sections="termsPrivacySections"
                :privacy-checkbox-label="termsPrivacyCheckbox"
                :terms-intro="termsIntro"
                :terms-sections="termsSections"
                :terms-checkbox-label="termsCheckbox"
                @close="showTermsModal = false"
                @accept="termsAccepted = true; showTermsModal = false"
            />
        </Modal>
    </div>
</template>
