<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AuthBase from '@/layouts/AuthLayout.vue';
import { register } from '@/routes';
import { store } from '@/routes/login';
import { request } from '@/routes/password';
import { PhFirstAid } from '@phosphor-icons/vue'

defineProps<{
    status?: string;
    canResetPassword: boolean;
    canRegister: boolean;
}>();
</script>

<template>
    <div className="flex bg-orange-400/75 ">
        <AuthBase className="bg-gray-400 allign-center w-fit h-screen p-10 flex justify-center"
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
                    <!-- <div class="grid gap-2">
                        <Label for="username">Username</Label>
                        <Input
                            id="username"
                            name="username"
                            required
                            autofocus
                            :tabindex="1"
                            autocomplete="username"
                            placeholder="username"
                        />
                        <InputError :message="errors.username" />
                    </div> -->
                    <div class="grid gap-2">
                        <Label for="email">Email Address</Label>
                        <Input class="bg-gray-200 border-gray-200"
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

                    <Button
                        type="submit"
                        class="mt-4 w-full rounded-full bg-orange-600"
                        :tabindex="4"
                        :disabled="processing"
                        data-test="login-button"
                    >
                        <Spinner v-if="processing" />
                        Sign In
                    </Button>
                </div>

                <div
                    class="text-center text-sm text-muted-foreground"
                    v-if="canRegister"
                >
                    Don't have an account?
                    <TextLink :href="register()" :tabindex="5">Sign up</TextLink>
                </div>
            </Form>
        </AuthBase>
        <div className="flex flex-1 flex-col items-center justify-center text-center gap-4">
            <div class="rounded-md bg-red-500">
                <PhFirstAid :size="52" weight="fill" class="text-red-500 bg-white rounded-full m-3"/>
            </div>
            <h1 className="text-5xl text-white font-black">
                Reliable Response,<br />
                Reliable Protection
            </h1>

            <p className="text-white/90 max-w-xl">
                Join the iKAPIT network and help us build safer, more resilient<br />
                communities through coordinated emergency response.
            </p>
        </div>
    </div>
</template>
