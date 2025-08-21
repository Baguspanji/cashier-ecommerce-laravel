<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import AuthBase from '@/layouts/AuthLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { LoaderCircle, Eye, EyeOff, Mail, Lock, User, UserPlus } from 'lucide-vue-next';
import { ref } from 'vue';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const showPassword = ref(false);
const showPasswordConfirmation = ref(false);

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};

const togglePasswordVisibility = () => {
    showPassword.value = !showPassword.value;
};

const togglePasswordConfirmationVisibility = () => {
    showPasswordConfirmation.value = !showPasswordConfirmation.value;
};
</script>

<template>
    <AuthBase title="Join us today" description="Create your account to get started">
        <Head title="Register" />

        <Card class="border-0 shadow-lg bg-background/80 backdrop-blur-sm">
            <CardHeader class="space-y-2 pb-6">
                <CardTitle class="text-2xl font-semibold text-center">Create Account</CardTitle>
                <CardDescription class="text-center text-muted-foreground">
                    Fill in your details to create your new account
                </CardDescription>
            </CardHeader>

            <CardContent>
                <form method="POST" @submit.prevent="submit" class="space-y-6">
                    <!-- Name Field -->
                    <div class="space-y-2">
                        <Label for="name" class="text-sm font-medium flex items-center gap-2">
                            <User class="h-4 w-4 text-muted-foreground" />
                            Full name
                        </Label>
                        <div class="relative">
                            <Input
                                id="name"
                                type="text"
                                required
                                autofocus
                                :tabindex="1"
                                autocomplete="name"
                                v-model="form.name"
                                placeholder="Enter your full name"
                                class="pl-10 h-11 transition-all duration-200 focus:ring-2 focus:ring-primary/20"
                                :class="{ 'border-destructive': form.errors.name }"
                            />
                            <User class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                        </div>
                        <InputError :message="form.errors.name" />
                    </div>

                    <!-- Email Field -->
                    <div class="space-y-2">
                        <Label for="email" class="text-sm font-medium flex items-center gap-2">
                            <Mail class="h-4 w-4 text-muted-foreground" />
                            Email address
                        </Label>
                        <div class="relative">
                            <Input
                                id="email"
                                type="email"
                                required
                                :tabindex="2"
                                autocomplete="email"
                                v-model="form.email"
                                placeholder="Enter your email"
                                class="pl-10 h-11 transition-all duration-200 focus:ring-2 focus:ring-primary/20"
                                :class="{ 'border-destructive': form.errors.email }"
                            />
                            <Mail class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                        </div>
                        <InputError :message="form.errors.email" />
                    </div>

                    <!-- Password Field -->
                    <div class="space-y-2">
                        <Label for="password" class="text-sm font-medium flex items-center gap-2">
                            <Lock class="h-4 w-4 text-muted-foreground" />
                            Password
                        </Label>
                        <div class="relative">
                            <Input
                                id="password"
                                :type="showPassword ? 'text' : 'password'"
                                required
                                :tabindex="3"
                                autocomplete="new-password"
                                v-model="form.password"
                                placeholder="Create a password"
                                class="pl-10 pr-12 h-11 transition-all duration-200 focus:ring-2 focus:ring-primary/20"
                                :class="{ 'border-destructive': form.errors.password }"
                            />
                            <Lock class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                            <button
                                type="button"
                                @click="togglePasswordVisibility"
                                class="absolute right-3 top-1/2 -translate-y-1/2 p-1 rounded-md hover:bg-muted transition-colors"
                                :tabindex="-1"
                            >
                                <Eye v-if="!showPassword" class="h-4 w-4 text-muted-foreground" />
                                <EyeOff v-else class="h-4 w-4 text-muted-foreground" />
                                <span class="sr-only">{{ showPassword ? 'Hide' : 'Show' }} password</span>
                            </button>
                        </div>
                        <InputError :message="form.errors.password" />
                    </div>

                    <!-- Confirm Password Field -->
                    <div class="space-y-2">
                        <Label for="password_confirmation" class="text-sm font-medium flex items-center gap-2">
                            <Lock class="h-4 w-4 text-muted-foreground" />
                            Confirm password
                        </Label>
                        <div class="relative">
                            <Input
                                id="password_confirmation"
                                :type="showPasswordConfirmation ? 'text' : 'password'"
                                required
                                :tabindex="4"
                                autocomplete="new-password"
                                v-model="form.password_confirmation"
                                placeholder="Confirm your password"
                                class="pl-10 pr-12 h-11 transition-all duration-200 focus:ring-2 focus:ring-primary/20"
                                :class="{ 'border-destructive': form.errors.password_confirmation }"
                            />
                            <Lock class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                            <button
                                type="button"
                                @click="togglePasswordConfirmationVisibility"
                                class="absolute right-3 top-1/2 -translate-y-1/2 p-1 rounded-md hover:bg-muted transition-colors"
                                :tabindex="-1"
                            >
                                <Eye v-if="!showPasswordConfirmation" class="h-4 w-4 text-muted-foreground" />
                                <EyeOff v-else class="h-4 w-4 text-muted-foreground" />
                                <span class="sr-only">{{ showPasswordConfirmation ? 'Hide' : 'Show' }} password</span>
                            </button>
                        </div>
                        <InputError :message="form.errors.password_confirmation" />
                    </div>

                    <!-- Submit Button -->
                    <Button
                        type="submit"
                        class="w-full h-11 text-base font-medium shadow-lg hover:shadow-xl transition-all duration-200"
                        :tabindex="5"
                        :disabled="form.processing"
                    >
                        <LoaderCircle v-if="form.processing" class="h-5 w-5 animate-spin mr-2" />
                        <UserPlus v-else class="h-5 w-5 mr-2" />
                        {{ form.processing ? 'Creating account...' : 'Create Account' }}
                    </Button>
                </form>

                <!-- Divider -->
                <div class="relative my-8">
                    <div class="absolute inset-0 flex items-center">
                        <span class="w-full border-t border-muted" />
                    </div>
                    <div class="relative flex justify-center text-xs uppercase">
                        <span class="bg-background px-2 text-muted-foreground">Already a member?</span>
                    </div>
                </div>

                <!-- Login Link -->
                <div class="text-center">
                    <p class="text-sm text-muted-foreground">
                        Already have an account?
                        <TextLink :href="route('login')" class="font-medium text-primary hover:text-primary/80 transition-colors ml-1">
                            Sign in here
                        </TextLink>
                    </p>
                </div>
            </CardContent>
        </Card>
    </AuthBase>
</template>
