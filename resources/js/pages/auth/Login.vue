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

defineProps<{
    status?: string;
    canResetPassword: boolean;
    canRegister: boolean;
}>();

const termsAccepted = ref(false);
const showTermsModal = ref(false);
</script>

<template>
    <div class="flex bg-orange-400/75 h-screen overflow-hidden">
        <AuthBase
            class="bg-gray-400 w-fit h-screen p-10 flex justify-center"
            title="Welcome Back!"
            description="Sign in to your account to continue"
        >
            <Head title="Log in" />

            <div
                v-if="status"
                class="mb-4 text-center text-sm font-medium text-green-600"
            >
                {{ status }}
            </div>

            <Form
                v-bind="store.form()"
                :reset-on-success="['password']"
                v-slot="{ errors, processing }"
                class="flex flex-col gap-6"
            >
                <div class="grid gap-6">
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

                    <div class="grid gap-2">
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
                        <Label for="remember" class="flex items-center space-x-3">
                            <Checkbox id="remember" name="remember" :tabindex="3" />
                            <span>Remember me</span>
                        </Label>
                    </div>

                    <!-- Terms and Conditions Checkbox -->
                    <div class="flex items-start space-x-3">
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
                        class="mt-4 w-full rounded-full bg-orange-600"
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

            <!-- Stats Grid (added below the form) -->
            <div class="grid grid-cols-2 gap-2 mt-6">
                <div class="bg-gray-300/60 rounded-lg p-3 text-center">
                    <div class="text-xl font-bold text-gray-900">24/7</div>
                    <div class="text-xs text-gray-600">Always on</div>
                </div>
                <div class="bg-gray-300/60 rounded-lg p-3 text-center">
                    <div class="text-xl font-bold text-gray-900">4</div>
                    <div class="text-xs text-gray-600">Major Emergency Type</div>
                </div>
                <div class="bg-gray-300/60 rounded-lg p-3 text-center">
                    <div class="text-sm font-bold text-gray-900">GEARS</div>
                    <div class="text-xs text-gray-600">Mobile App</div>
                </div>
                <div class="bg-gray-300/60 rounded-lg p-3 text-center">
                    <div class="text-sm font-bold text-gray-900">MDRRMO</div>
                    <div class="text-xs text-gray-600">Centralized System</div>
                </div>
            </div>
        </AuthBase>

        <div class="flex flex-1 flex-col items-center justify-center text-center gap-4">
            <div class="rounded-md bg-red-500">
                <PhFirstAid :size="52" weight="fill" class="text-red-500 bg-white rounded-full m-3" />
            </div>
            <h1 class="text-5xl text-white font-black">
                Reliable Response,<br />
                Reliable Protection
            </h1>
            <p class="text-white/90 max-w-xl">
                Join the GEARS network and help us build safer, more resilient<br />
                communities through coordinated emergency response.
            </p>

            <!-- Feature Cards Grid (added below the text) -->
            <div class="grid grid-cols-2 gap-3 max-w-lg w-full mt-2">
                <div class="bg-white/20 rounded-xl p-5 text-center">
                    <PhPhone :size="28" weight="fill" class="text-white mx-auto mb-2" />
                    <div class="text-sm font-bold text-white mb-1">One-tap reporting</div>
                    <div class="text-xs text-white/85 leading-relaxed">
                        Report any emergency in seconds using just a photo and your location
                    </div>
                </div>
                <div class="bg-white/20 rounded-xl p-5 text-center">
                    <PhBell :size="28" weight="fill" class="text-white mx-auto mb-2" />
                    <div class="text-sm font-bold text-white mb-1">Instant Alerts</div>
                    <div class="text-xs text-white/85 leading-relaxed">
                        Community-wide notifications keep all GMA residents informed and prepared
                    </div>
                </div>
                <div class="bg-white/20 rounded-xl p-5 text-center">
                    <PhMapPin :size="28" weight="fill" class="text-white mx-auto mb-2" />
                    <div class="text-sm font-bold text-white mb-1">Live Incident Map</div>
                    <div class="text-xs text-white/85 leading-relaxed">
                        Track emergencies and responders location across GMA
                    </div>
                </div>
                <div class="bg-white/20 rounded-xl p-5 text-center">
                    <PhClipboardText :size="28" weight="fill" class="text-white mx-auto mb-2" />
                    <div class="text-sm font-bold text-white mb-1">Priority-ranked Incidents</div>
                    <div class="text-xs text-white/85 leading-relaxed">
                        Incidents are ranked using AHP-TOPSIS scoring
                    </div>
                </div>
            </div>
            <div class="text-white font-black text-sm mt-10">Powered by GMA Cavite MDRRMO - Serving all 55 berangays of General Mariano Alvarez</div>
        </div>

        <!-- Terms and Conditions Modal -->
        <Modal :show="showTermsModal" @close="showTermsModal = false" maxWidth="2xl">
            <TermsAndConditions
                @close="showTermsModal = false"
                @accept="termsAccepted = true; showTermsModal = false"
            />
        </Modal>
    </div>
</template>
