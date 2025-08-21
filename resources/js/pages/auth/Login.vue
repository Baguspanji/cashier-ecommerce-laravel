<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import AuthBase from '@/layouts/AuthLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { LoaderCircle, Eye, EyeOff, Mail, Lock, Shield } from 'lucide-vue-next';
import { ref } from 'vue';

defineProps<{
    status?: string;
    canResetPassword: boolean;
}>();

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const showPassword = ref(false);

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};

const togglePasswordVisibility = () => {
    showPassword.value = !showPassword.value;
};
</script>

<template>
    <AuthBase title="Selamat Datang Kembali" description="Masuk ke akun Anda untuk melanjutkan">

        <Head title="Login" />

        <!-- Status Message -->
        <div v-if="status"
            class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg dark:bg-green-900/20 dark:border-green-800">
            <div class="flex items-center gap-3">
                <Shield class="h-5 w-5 text-green-500 dark:text-green-400" />
                <p class="text-sm font-medium text-green-800 dark:text-green-200">
                    {{ status }}
                </p>
            </div>
        </div>

        <Card class="border-0 shadow-lg bg-background/80 backdrop-blur-sm">
            <CardHeader class="space-y-2 pb-2">
                <CardTitle class="text-2xl font-semibold text-center">Cashier Store</CardTitle>
                <CardDescription class="text-center text-muted-foreground">
                    Masukkan kredensial Anda untuk mengakses akun Anda
                </CardDescription>
            </CardHeader>

            <CardContent>
                <form method="POST" @submit.prevent="submit" class="space-y-6">
                    <!-- Email Field -->
                    <div class="space-y-2">
                        <Label for="email" class="text-sm font-medium flex items-center gap-2">
                            Alamat Email
                        </Label>
                        <div class="relative">
                            <Input id="email" type="email" required autofocus :tabindex="1" autocomplete="email"
                                v-model="form.email" placeholder="Masukkan email Anda"
                                class="pl-10 h-11 transition-all duration-200 focus:ring-2 focus:ring-primary/20"
                                :class="{ 'border-destructive': form.errors.email }" />
                            <Mail class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                        </div>
                        <InputError :message="form.errors.email" />
                    </div>

                    <!-- Password Field -->
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <Label for="password" class="text-sm font-medium flex items-center gap-2">
                                Kata Sandi
                            </Label>
                        </div>
                        <div class="relative">
                            <Input id="password" :type="showPassword ? 'text' : 'password'" required :tabindex="2"
                                autocomplete="current-password" v-model="form.password"
                                placeholder="Enter your password"
                                class="pl-10 pr-12 h-11 transition-all duration-200 focus:ring-2 focus:ring-primary/20"
                                :class="{ 'border-destructive': form.errors.password }" />
                            <Lock class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                            <button type="button" @click="togglePasswordVisibility"
                                class="absolute right-3 top-1/2 -translate-y-1/2 p-1 rounded-md hover:bg-muted transition-colors"
                                :tabindex="-1">
                                <Eye v-if="!showPassword" class="h-4 w-4 text-muted-foreground" />
                                <EyeOff v-else class="h-4 w-4 text-muted-foreground" />
                                <span class="sr-only">{{ showPassword ? 'Hide' : 'Show' }} password</span>
                            </button>
                        </div>
                        <InputError :message="form.errors.password" />
                    </div>

                    <!-- Remember Me -->
                    <div class="flex items-center justify-between py-2">
                        <Label for="remember" class="flex items-center space-x-3 cursor-pointer">
                            <Checkbox id="remember" v-model="form.remember" :tabindex="3" />
                            <span class="text-sm text-muted-foreground">Ingat saya selama 30 hari</span>
                        </Label>
                    </div>

                    <!-- Submit Button -->
                    <Button type="submit"
                        class="w-full h-11 text-base font-medium shadow-lg hover:shadow-xl transition-all duration-200"
                        :tabindex="4" :disabled="form.processing">
                        <LoaderCircle v-if="form.processing" class="h-5 w-5 animate-spin mr-2" />
                        <Shield v-else class="h-5 w-5 mr-2" />
                        {{ form.processing ? 'Masuk...' : 'Masuk' }}
                    </Button>
                </form>
            </CardContent>
        </Card>
    </AuthBase>
</template>
