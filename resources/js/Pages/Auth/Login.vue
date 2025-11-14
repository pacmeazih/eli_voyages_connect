<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';

defineProps({
    canResetPassword: Boolean,
    status: String,
});

const form = useForm({
    login: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};

// Demo login helper
const loginAsDemo = (login, password) => {
    form.login = login;
    form.password = password;
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
  <Head title="Login" />
  <div class="min-h-screen flex flex-col lg:flex-row bg-gray-100">
    <!-- Left panel: form -->
    <div class="w-full lg:w-1/2 flex items-center justify-center py-12 px-6 lg:px-12 bg-white">
      <div class="max-w-md w-full">
                    <!-- Logo Circle -->
            <div class="mb-8 flex justify-center">
                <div class="relative">
                    <div class="h-40 w-40 rounded-full bg-amber-200 dark:bg-amber-900/30 flex items-center justify-center ring-4 ring-amber-400 dark:ring-amber-600 shadow-xl">
                        <img 
                            src="/assets/img/branding/Eli-Voyages icon.png" 
                            alt="ELI VOYAGES" 
                            class="h-32 w-32 object-contain"
                        >
                    </div>
                </div>
            </div>

        <h1 class="text-center text-3xl font-bold text-gray-800 mb-2">Bienvenue ! <span class="inline-block animate-pulse">👋</span></h1>
        <p class="text-center text-sm text-gray-600 mb-6">
          Vous êtes sur le portail client<br />
          <span class="font-semibold">d'ELI – VOYAGES SARL</span>
        </p>

        <!-- Status -->
        <div v-if="status" class="mb-4 text-sm text-green-700 bg-green-50 border border-green-200 rounded p-3">
          {{ status }}
        </div>

        <form @submit.prevent="submit" class="space-y-5">
          <div>
            <label for="login" class="block text-sm font-medium text-gray-700 mb-1">
              Code client ou Email
            </label>
            <input
              id="login"
              v-model="form.login"
              type="text"
              required
              autocomplete="username"
              class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
              :class="{ 'border-red-500': form.errors.login }"
              placeholder="ELV-2025-001 ou email@example.com"
            />
            <p v-if="form.errors.login" class="mt-1 text-xs text-red-600">{{ form.errors.login }}</p>
          </div>

          <div>
            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Mot de passe</label>
            <input
              id="password"
              v-model="form.password"
              type="password"
              required
              autocomplete="current-password"
              class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
              :class="{ 'border-red-500': form.errors.password }"
              placeholder="Veuillez saisir votre mot de passe"
            />
            <p v-if="form.errors.password" class="mt-1 text-xs text-red-600">{{ form.errors.password }}</p>
          </div>

          <div class="flex items-center justify-between">
            <label class="flex items-center gap-2 text-sm text-gray-700">
              <input type="checkbox" v-model="form.remember" class="rounded text-amber-600 focus:ring-amber-500" />
              Se souvenir de moi
            </label>
            <div v-if="canResetPassword">
              <Link :href="route('password.request')" class="text-xs font-medium text-amber-700 hover:text-amber-600">Mot de passe oublié ?</Link>
            </div>
          </div>

          <PrimaryButton
            type="submit"
            class="w-full justify-center bg-amber-600 hover:bg-amber-700 text-white font-semibold py-2.5 rounded-md shadow focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500 transition"
            :class="{ 'opacity-25': form.processing }"
            :disabled="form.processing"
          >
            <span v-if="form.processing">Connexion...</span>
            <span v-else>Se connecter</span>
          </PrimaryButton>
        </form>

        <!-- Demo Mode Login Buttons -->
        <div class="mt-8 border-t border-gray-200 pt-6">
          <p class="text-center text-xs text-gray-500 mb-4 font-medium uppercase tracking-wide">🎭 Mode Démo</p>
          <div class="grid grid-cols-2 gap-3">
            <!-- Admin Demo -->
            <button
              @click="loginAsDemo('admin@elivoyages.com', 'password')"
              class="relative overflow-hidden px-3 py-2 text-xs font-semibold text-white rounded-lg shadow-md transition-all duration-300 transform hover:scale-105 active:scale-95 bg-gradient-to-r from-purple-600 to-purple-800 hover:from-purple-700 hover:to-purple-900"
            >
              <span class="relative z-10">👑 Admin</span>
            </button>

            <!-- Agent Demo -->
            <button
              @click="loginAsDemo('agent@elivoyages.com', 'password')"
              class="relative overflow-hidden px-3 py-2 text-xs font-semibold text-white rounded-lg shadow-md transition-all duration-300 transform hover:scale-105 active:scale-95 bg-gradient-to-r from-blue-600 to-blue-800 hover:from-blue-700 hover:to-blue-900"
            >
              <span class="relative z-10">🎯 Agent</span>
            </button>

            <!-- Client Demo -->
            <button
              @click="loginAsDemo('client@elivoyages.com', 'password')"
              class="relative overflow-hidden px-3 py-2 text-xs font-semibold text-white rounded-lg shadow-md transition-all duration-300 transform hover:scale-105 active:scale-95 bg-gradient-to-r from-green-600 to-green-800 hover:from-green-700 hover:to-green-900"
            >
              <span class="relative z-10">👤 Client</span>
            </button>

            <!-- Consultant Demo -->
            <button
              @click="loginAsDemo('consultant@elivoyages.com', 'password')"
              class="relative overflow-hidden px-3 py-2 text-xs font-semibold text-white rounded-lg shadow-md transition-all duration-300 transform hover:scale-105 active:scale-95 bg-gradient-to-r from-amber-600 to-amber-800 hover:from-amber-700 hover:to-amber-900"
            >
              <span class="relative z-10">💼 Consultant</span>
            </button>
          </div>
        </div>

        <p class="mt-10 text-center text-xs text-gray-500">© 2025 SOUS TOUTES RÉSERVES</p>
      </div>
    </div>

    <!-- Right panel: marketing image / quote -->
    <div class="hidden lg:block lg:w-1/2 relative overflow-hidden">
      <div class="absolute inset-0 bg-amber-600/90 mix-blend-multiply"></div>
      <img src="https://images.unsplash.com/photo-1505761671935-60b3a7427bad?auto=format&fit=crop&w=1200&q=60" alt="City" class="h-full w-full object-cover" />
      <div class="absolute bottom-10 left-1/2 -translate-x-1/2 w-[85%] bg-gradient-to-br from-amber-700/90 to-orange-800/90 rounded-xl p-8 text-amber-100 shadow-lg backdrop-blur-sm">
        <p class="text-sm leading-relaxed">
          Nous ne faisons pas que vous accompagner :<br />
          nous vous aidons à imaginer, planifier et bâtir<br />
          votre avenir.
        </p>
        <div class="mt-6 text-xs">
          <p class="font-semibold">Pacôme Azih, PMP</p>
          <p>Président</p>
        </div>
      </div>
    </div>
  </div>
</template>
