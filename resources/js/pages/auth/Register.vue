<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthBase from '@/layouts/AuthLayout.vue';
import AccountTypeSelection from '@/pages/auth/AccountTypeSelection.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

// Define props
const props = defineProps<{
    prefilled_identifier?: string;
    identifier_type?: string;
    identifier_id?: number | string;
}>();

// Track the current step of the registration process
const currentStep = ref<'account-type' | 'terms' | 'details'>('account-type');

// Determine if we're using email or phone
const isEmail = computed(() => !props.identifier_type || props.identifier_type === 'email');

// Initialize form with appropriate fields
const form = useForm({
    given_name: '',
    family_name: '',
    email: props.prefilled_identifier && isEmail.value ? props.prefilled_identifier : '',
    phone: props.prefilled_identifier && !isEmail.value ? props.prefilled_identifier : '',
    password: '',
    password_confirmation: '',
    birthday: '',
    account_type: '', // Will be set when user selects account type
    identifier_type: props.identifier_type || '',
    identifier_id: props.identifier_id || '',
    terms_accepted: false,
});

// Computed properties for account type requirements
const isGuest = computed(() => form.account_type === 'guest');
const ageRequirement = computed(() => {
    if (form.account_type === 'guardian') return '18+';
    if (form.account_type === 'crew' || form.account_type === 'membership') return '13+';
    return '';
});

// Handle account type selection
const handleAccountTypeSelect = (type: string) => {
    form.account_type = type;
    currentStep.value = 'terms';
};

// Handle terms acceptance and proceed to details
const handleTermsAccept = () => {
    if (form.terms_accepted) {
        currentStep.value = 'details';
    }
};

// Watch for account type changes to update validation
watch(() => form.account_type, () => {
    // Reset validation errors when account type changes
    form.clearErrors();
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <!-- Step 1: Account Type Selection -->
    <AccountTypeSelection
        v-if="currentStep === 'account-type'"
        :selectedType="form.account_type"
        @select="handleAccountTypeSelect"
    />

    <!-- Step 2: Terms and Agreement -->
    <AuthBase
        v-else-if="currentStep === 'terms'"
        :title="`Terms and Agreement`"
        description="Please review and accept the terms and conditions"
    >
        <Head title="Terms and Agreement" />

        <div class="flex flex-col gap-6">
            <div class="grid gap-6">
                <!-- Account type display -->
                <div class="flex justify-between items-center">
                    <div>
                        <span class="text-sm text-muted-foreground">Account Type:</span>
                        <span class="ml-2 font-medium capitalize">{{ form.account_type }}</span>
                    </div>
                    <Button
                        type="button"
                        variant="ghost"
                        size="sm"
                        @click="currentStep = 'account-type'"
                    >
                        Change
                    </Button>
                </div>

                <!-- Terms and Agreement -->
                <div class="grid gap-4">
                    <div v-if="form.account_type" class="text-sm mb-4 p-4 bg-muted rounded-md">
                        {{ $t(`Terms ${form.account_type === 'membership' ? 'members' : form.account_type}`) }}
                    </div>
                    <div class="flex items-center space-x-2">
                        <Checkbox id="terms" v-model="form.terms_accepted" required />
                        <Label for="terms" class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                            {{ $t(`Agreement ${form.account_type === 'membership' ? 'members' : form.account_type}`) }}
                        </Label>
                    </div>
                    <InputError :message="form.errors.terms_accepted" />
                </div>

                <div class="flex justify-between mt-6">
                    <Button
                        type="button"
                        variant="outline"
                        @click="currentStep = 'account-type'"
                    >
                        Back
                    </Button>
                    <Button
                        type="button"
                        @click="handleTermsAccept"
                        :disabled="!form.terms_accepted"
                    >
                        Continue
                    </Button>
                </div>
            </div>

            <div class="text-center text-sm text-muted-foreground">
                Already have an account?
                <TextLink :href="route('login')" class="underline underline-offset-4">Log in</TextLink>
            </div>
        </div>
    </AuthBase>

    <!-- Step 3: Registration Details -->
    <AuthBase
        v-else
        :title="`Create ${form.account_type} account`"
        description="Enter your details below to create your account"
    >
        <Head title="Register" />

        <form @submit.prevent="submit" class="flex flex-col gap-6">
            <div class="grid gap-6">
                <!-- Account type display -->
                <div class="flex justify-between items-center">
                    <div>
                        <span class="text-sm text-muted-foreground">Account Type:</span>
                        <span class="ml-2 font-medium capitalize">{{ form.account_type }}</span>
                    </div>
                    <Button
                        type="button"
                        variant="ghost"
                        size="sm"
                        @click="currentStep = 'account-type'"
                    >
                        Change
                    </Button>
                </div>

                <div class="grid gap-2">
                    <Label for="given_name">First Name</Label>
                    <Input id="given_name" type="text" required autofocus :tabindex="1" autocomplete="given-name" v-model="form.given_name" placeholder="First name" />
                    <InputError :message="form.errors.given_name" />
                </div>

                <div class="grid gap-2">
                    <Label for="family_name">Last Name</Label>
                    <Input id="family_name" type="text" required :tabindex="2" autocomplete="family-name" v-model="form.family_name" placeholder="Last name" />
                    <InputError :message="form.errors.family_name" />
                </div>

                <div class="grid gap-2">
                    <Label for="birthday">Birthday <span v-if="ageRequirement" class="text-sm text-muted-foreground">({{ ageRequirement }})</span></Label>
                    <Input id="birthday" type="date" required :tabindex="3" v-model="form.birthday" />
                    <InputError :message="form.errors.birthday" />
                </div>

                <!-- Email field -->
                <div class="grid gap-2">
                    <Label for="email">
                        Email address
                        <span v-if="isGuest" class="text-sm text-muted-foreground">(required if no phone)</span>
                    </Label>
                    <Input
                        id="email"
                        type="email"
                        :required="!isGuest || !form.phone"
                        :tabindex="4"
                        autocomplete="email"
                        v-model="form.email"
                        placeholder="email@example.com"
                        :disabled="!!props.prefilled_identifier && isEmail"
                    />
                    <InputError :message="form.errors.email" />
                </div>

                <!-- Phone field -->
                <div class="grid gap-2">
                    <Label for="phone">
                        Phone number
                        <span v-if="isGuest" class="text-sm text-muted-foreground">(required if no email)</span>
                    </Label>
                    <Input
                        id="phone"
                        type="tel"
                        :required="!isGuest || !form.email"
                        :tabindex="5"
                        autocomplete="tel"
                        v-model="form.phone"
                        placeholder="+1 123 456 7890"
                        :disabled="!!props.prefilled_identifier && !isEmail"
                    />
                    <InputError :message="form.errors.phone" />
                </div>

                <div class="grid gap-2">
                    <Label for="password">
                        Password
                        <span v-if="isGuest" class="text-sm text-muted-foreground">(optional for guests)</span>
                    </Label>
                    <Input
                        id="password"
                        type="password"
                        :required="!isGuest"
                        :tabindex="6"
                        autocomplete="new-password"
                        v-model="form.password"
                        placeholder="Password"
                    />
                    <InputError :message="form.errors.password" />
                </div>

                <div class="grid gap-2">
                    <Label for="password_confirmation">Confirm password</Label>
                    <Input
                        id="password_confirmation"
                        type="password"
                        :required="!isGuest"
                        :tabindex="7"
                        autocomplete="new-password"
                        v-model="form.password_confirmation"
                        placeholder="Confirm password"
                    />
                    <InputError :message="form.errors.password_confirmation" />
                </div>

                <!-- Hidden fields for identifier information -->
                <input type="hidden" v-model="form.identifier_type" />
                <input type="hidden" v-model="form.identifier_id" />
                <input type="hidden" v-model="form.terms_accepted" />

                <div class="flex justify-between mt-6">
                    <Button
                        type="button"
                        variant="outline"
                        @click="currentStep = 'terms'"
                    >
                        Back
                    </Button>
                    <Button type="submit" class="w-full ml-4" tabindex="8" :disabled="form.processing">
                        <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
                        Create account
                    </Button>
                </div>
            </div>

            <div class="text-center text-sm text-muted-foreground">
                Already have an account?
                <TextLink :href="route('login')" class="underline underline-offset-4" :tabindex="9">Log in</TextLink>
            </div>
        </form>
    </AuthBase>
</template>
