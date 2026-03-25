<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AuthBase from '@/layouts/AuthLayout.vue';
import { login } from '@/routes';
import { store } from '@/routes/register';
</script>

<template>
    <AuthBase
        title="Create an account"
        description="Enter your details below to create your account"
    >
        <Head title="Register" />

        <Form
            v-bind="store.form()"
            :reset-on-success="['password', 'password_confirmation']"
            v-slot="{ errors, processing }"
            class="flex flex-col gap-6"
        >
            <div class="grid grid-cols-2 gap-4">

                <!-- Full Name -->
                <div class="col-span-2 grid gap-2">
                    <Label for="name">Full name</Label>
                    <Input
                        id="name"
                        type="text"
                        required
                        autofocus
                        :tabindex="1"
                        autocomplete="name"
                        name="name"
                        placeholder="Juan S. Dela Cruz"
                    />
                    <InputError :message="errors.name" />
                </div>

                <!-- First Name -->
                <div class="grid gap-2">
                    <Label for="first_name">First name</Label>
                    <Input
                        id="first_name"
                        type="text"
                        :tabindex="2"
                        name="first_name"
                        placeholder="Juan"
                    />
                    <InputError :message="errors.first_name" />
                </div>

                <!-- Middle Name -->
                <div class="grid gap-2">
                    <Label for="middle_name">Middle name</Label>
                    <Input
                        id="middle_name"
                        type="text"
                        :tabindex="3"
                        name="middle_name"
                        placeholder="Santos"
                    />
                    <InputError :message="errors.middle_name" />
                </div>

                <!-- Last Name -->
                <div class="grid gap-2">
                    <Label for="last_name">Last name</Label>
                    <Input
                        id="last_name"
                        type="text"
                        :tabindex="4"
                        name="last_name"
                        placeholder="Dela Cruz"
                    />
                    <InputError :message="errors.last_name" />
                </div>

                <!-- Email -->
                <div class="grid gap-2">
                    <Label for="email">Email address</Label>
                    <Input
                        id="email"
                        type="email"
                        :tabindex="5"
                        autocomplete="email"
                        name="email"
                        placeholder="email@example.com"
                    />
                    <InputError :message="errors.email" />
                </div>

                <!-- Mobile No -->
                <div class="grid gap-2">
                    <Label for="mobile_no">Mobile number</Label>
                    <Input
                        id="mobile_no"
                        type="text"
                        :tabindex="6"
                        name="mobile_no"
                        placeholder="09XX XXX XXXX"
                    />
                    <InputError :message="errors.mobile_no" />
                </div>

                <!-- Birth Date -->
                <div class="grid gap-2">
                    <Label for="birth_date">Birth date</Label>
                    <Input
                        id="birth_date"
                        type="date"
                        :tabindex="7"
                        name="birth_date"
                    />
                    <InputError :message="errors.birth_date" />
                </div>

                <!-- Address -->
                <div class="col-span-2 grid gap-2">
                    <Label for="address">Address</Label>
                    <Input
                        id="address"
                        type="text"
                        :tabindex="8"
                        name="address"
                        placeholder="Street, Barangay, City"
                    />
                    <InputError :message="errors.address" />
                </div>

                <!-- Username -->
                <div class="col-span-2 grid gap-2">
                    <Label for="username">Username</Label>
                    <Input
                        id="username"
                        type="text"
                        required
                        :tabindex="9"
                        autocomplete="username"
                        name="username"
                        placeholder="username"
                    />
                    <InputError :message="errors.username" />
                </div>

                <!-- Password -->
                <div class="grid gap-2">
                    <Label for="password">Password</Label>
                    <Input
                        id="password"
                        type="password"
                        required
                        :tabindex="10"
                        autocomplete="new-password"
                        name="password"
                        placeholder="Password"
                    />
                    <InputError :message="errors.password" />
                </div>

                <!-- Confirm Password -->
                <div class="grid gap-2">
                    <Label for="password_confirmation">Confirm password</Label>
                    <Input
                        id="password_confirmation"
                        type="password"
                        required
                        :tabindex="11"
                        autocomplete="new-password"
                        name="password_confirmation"
                        placeholder="Confirm password"
                    />
                    <InputError :message="errors.password_confirmation" />
                </div>

            </div>

            <Button
                type="submit"
                class="mt-2 w-full"
                tabindex="12"
                :disabled="processing"
                data-test="register-user-button"
            >
                <Spinner v-if="processing" />
                Create account
            </Button>

            <div class="text-center text-sm text-muted-foreground">
                Already have an account?
                <TextLink
                    :href="login()"
                    class="underline underline-offset-4"
                    :tabindex="13"
                >Log in</TextLink>
            </div>
        </Form>
    </AuthBase>
</template>
