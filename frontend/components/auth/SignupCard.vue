<script setup lang="ts">
import { useField, required, email as emailRule, min } from '~/composables/useFieldValidation'

defineProps<{
  loading: boolean
}>()

const emit = defineEmits<{
  submit: [{ name: string; email: string; password: string }]
}>()

const {
  value: name,
  error: nameError,
  touch: touchName,
  validate: validateName,
} = useField('', [required('Le nom est requis')])

const {
  value: email,
  error: emailError,
  touch: touchEmail,
  validate: validateEmail,
} = useField('', [required(), emailRule('Adresse email invalide')])

const {
  value: password,
  error: passwordError,
  touch: touchPassword,
  validate: validatePassword,
} = useField('', [required(), min(8, 'Le mot de passe doit contenir au moins 8 caractères')])

function handleSubmit() {
  const valid = [validateName, validateEmail, validatePassword].every((fn) => fn())
  if (valid) {
    emit('submit', { name: name.value, email: email.value, password: password.value })
  }
}
</script>

<template>
  <div class="bg-white rounded-2xl p-8 w-full max-w-sm shadow-sm space-y-5">
    <div class="flex flex-col items-center gap-3">
      <AppLogo size="md" />
      <h1 class="text-lg font-semibold text-gray-900 dark:text-white">
        Créer un compte
      </h1>
    </div>

    <form class="space-y-4" novalidate @submit.prevent="handleSubmit">
      <Input
        v-model="name"
        type="text"
        name="name"
        label="Nom complet"
        placeholder="Jean Dupont"
        :error="nameError"
        :disabled="loading"
        @blur="touchName()"
      />

      <Input
        v-model="email"
        type="email"
        name="email"
        label="Email"
        placeholder="jean@exemple.com"
        :error="emailError"
        :disabled="loading"
        @blur="touchEmail()"
      />

      <Input
        v-model="password"
        type="password"
        name="password"
        label="Mot de passe"
        placeholder="8 caractères minimum"
        :error="passwordError"
        :disabled="loading"
        @blur="touchPassword()"
      />

      <Button
        type="submit"
        variant="primary"
        full-width
        :loading="loading"
      >
        Créer mon compte
      </Button>
    </form>

    <p class="text-center text-sm text-gray-500">
      Déjà un compte ?
      <NuxtLink to="/auth/login" class="font-medium text-brand-500 hover:underline">
        Se connecter
      </NuxtLink>
    </p>
  </div>
</template>
