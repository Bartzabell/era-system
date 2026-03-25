<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import { useForm } from '@inertiajs/vue3'
import AuthBase from '@/layouts/AuthLayout.vue'
import CustomInput from '@/components/CustomInput.vue'
import Boombox from '@/components/BoomBox.vue'
import ButtonCode from '@/components/ButtonCode.vue'
import TextLink from '@/components/TextLink.vue'
import { PhUserPlus, PhFirstAid } from '@phosphor-icons/vue'
import { login } from '@/routes'

const props = withDefaults(defineProps<{
    barangays: Array<{ id: number; barangay_name: string }>
}>(), {
    barangays: () => []
})

const form = useForm({
    username:              '',
    password:              '',
    password_confirmation: '',
    first_name:            '',
    middle_name:           '',
    last_name:             '',
    email:                 '',
    mobile_no:             '',
    birth_date:            '',
    address:               '',
    barangay_id:           null as number | null,
})



const submit = () => {
    form.post('/register', {
        onFinish: () => form.reset('password', 'password_confirmation'),
    })
}
</script>

<template>
    <div class="flex min-h-screen">
        <Head title="Register" />

        <!-- Left panel — decorative, matches your login style -->
        <div class="hidden lg:flex flex-1 flex-col items-center justify-center text-center gap-4 bg-orange-500/90 p-12">
            <div class="rounded-md bg-red-500">
                <PhFirstAid :size="52" weight="fill" class="text-red-500 bg-white rounded-full m-3" />
            </div>
            <h1 class="text-5xl text-white font-black leading-tight">
                Join the Network.<br />
                Protect Your Community.
            </h1>
            <p class="text-white/90 max-w-xl">
                Create your iKAPIT account and become part of a coordinated emergency
                response system built for safer, more resilient communities.
            </p>
        </div>

        <!-- Right panel — form -->
        <div class="flex flex-1 items-center justify-center bg-white dark:bg-gray-900 p-6 overflow-y-auto">
            <div class="w-full max-w-2xl">

                <!-- Brand header -->
                <div class="mb-8 text-center lg:text-left">
                    <h2 class="text-3xl font-extrabold text-gray-800 dark:text-white">Create an account</h2>
                    <p class="text-sm text-gray-500 mt-1">Fill in your details to get started</p>
                </div>

                <form @submit.prevent="submit" class="space-y-6">

                    <!-- Account Info -->
                    <section>
                        <h3 class="mb-2 font-semibold text-gray-700 dark:text-gray-200">Account Info</h3>
                        <div class="grid grid-cols-1 gap-3 p-4 border border-dashed border-gray-300 rounded-lg lg:grid-cols-2">

                            <div class="lg:col-span-2">
                                <CustomInput name="Email" type="email" v-model="form.email" placeholder="email@example.com" />
                                <p v-if="form.errors.email" class="text-xs text-red-500 mt-1">{{ form.errors.email }}</p>
                            </div>

                            <div>
                                <CustomInput name="Username" v-model="form.username" placeholder="username" />
                                <p v-if="form.errors.username" class="text-xs text-red-500 mt-1">{{ form.errors.username }}</p>
                            </div>

                            <div><!-- spacer on desktop --></div>

                            <div>
                                <CustomInput name="Password" type="password" v-model="form.password" />
                                <p v-if="form.errors.password" class="text-xs text-red-500 mt-1">{{ form.errors.password }}</p>
                            </div>

                            <div>
                                <CustomInput name="Confirm Password" type="password" v-model="form.password_confirmation" />
                                <p v-if="form.errors.password_confirmation" class="text-xs text-red-500 mt-1">{{ form.errors.password_confirmation }}</p>
                            </div>

                        </div>
                    </section>

                    <!-- Personal Info -->
                    <section>
                        <h3 class="mb-2 font-semibold text-gray-700 dark:text-gray-200">Personal Info</h3>
                        <div class="grid grid-cols-1 gap-3 p-4 border border-dashed border-gray-300 rounded-lg lg:grid-cols-2">

                            <div>
                                <CustomInput name="First Name" v-model="form.first_name" placeholder="Juan" />
                                <p v-if="form.errors.first_name" class="text-xs text-red-500 mt-1">{{ form.errors.first_name }}</p>
                            </div>

                            <div>
                                <CustomInput name="Middle Name" v-model="form.middle_name" placeholder="Santos" />
                            </div>

                            <div>
                                <CustomInput name="Last Name" v-model="form.last_name" placeholder="Dela Cruz" />
                                <p v-if="form.errors.last_name" class="text-xs text-red-500 mt-1">{{ form.errors.last_name }}</p>
                            </div>

                            <div>
                                <CustomInput name="Mobile No." v-model="form.mobile_no" placeholder="09XX XXX XXXX" />
                                <p v-if="form.errors.mobile_no" class="text-xs text-red-500 mt-1">{{ form.errors.mobile_no }}</p>
                            </div>

                            <div>
                                <CustomInput name="Birth Date" type="date" v-model="form.birth_date" />
                                <p v-if="form.errors.birth_date" class="text-xs text-red-500 mt-1">{{ form.errors.birth_date }}</p>
                            </div>

                            <div>
                                <CustomInput name="Address" v-model="form.address" placeholder="Street, Barangay, City" />
                            </div>

                            <div class="lg:col-span-2">
                                <label class="block m-1 text-sm text-gray-600 dark:text-gray-200">Barangay</label>
                                <Boombox
                                    :items="props.barangays ?? []"
                                    label-field="barangay_name"
                                    placeholder="Select your barangay"
                                    @change="(v: any) => form.barangay_id = v?.id ?? null"
                                />
                            </div>

                        </div>
                    </section>

                    <!-- Submit -->
                    <div class="flex flex-col items-center gap-3">
                        <ButtonCode
                            type="submit"
                            :icon="PhUserPlus"
                            color="bg-orange-500 hover:bg-orange-600"
                            text="Create Account"
                            :disabled="form.processing"
                            class="w-full"
                        />

                        <p class="text-sm text-gray-500">
                            Already have an account?
                            <TextLink :href="login()" class="underline underline-offset-4 text-orange-600">
                                Log in
                            </TextLink>
                        </p>
                    </div>

                </form>
            </div>
        </div>
    </div>
</template>
