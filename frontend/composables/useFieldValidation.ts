/**
 * Approche ultra-simple inspirée de Quasar
 * Règles = fonctions pures qui retournent true ou un message d'erreur
 */

export type SimpleRule = (val: string) => true | string

/**
 * Règles de validation simples (style Quasar pur)
 */

// Champ requis
export const required =
  (msg = 'Ce champ est requis'): SimpleRule =>
  (val) =>
    !!val || msg

// Email valide
export const email = (msg = 'Email invalide'): SimpleRule => {
  const EMAIL_REGEX = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  return (val) => EMAIL_REGEX.test(val) || msg
}

// Longueur minimale
export const min =
  (length: number, msg?: string): SimpleRule =>
  (val) =>
    val.length >= length || msg || `Minimum ${length} caractères`

// Longueur maximale
export const max =
  (length: number, msg?: string): SimpleRule =>
  (val) =>
    val.length <= length || msg || `Maximum ${length} caractères`

// Téléphone français
export const phoneFR = (msg = 'Téléphone invalide'): SimpleRule => {
  const PHONE_REGEX = /^(?:(?:\+|00)33|0)\s*[1-9](?:[\s.-]*\d{2}){4}$/
  return (val) => !val || PHONE_REGEX.test(val) || msg
}

// Correspondance
export const sameAs =
  (getValue: () => string, msg = 'Les valeurs ne correspondent pas'): SimpleRule =>
  (val) =>
    val === getValue() || msg

// Pattern personnalisé
export const regex =
  (pattern: RegExp, msg = 'Format invalide'): SimpleRule =>
  (val) =>
    pattern.test(val) || msg

/**
 * Valide une valeur avec un tableau de règles
 * Retourne le premier message d'erreur ou undefined
 */
export const validate = (value: string, rules: SimpleRule[]): string | undefined => {
  for (const rule of rules) {
    const result = rule(value)
    if (result !== true) {
      return result
    }
  }
  return undefined
}

/**
 * Composable minimaliste avec validation simple
 * Combine approche Quasar (règles simples) + state Vue
 */
export const useField = (initialValue = '', rules: SimpleRule[] = []) => {
  const value = ref(initialValue)
  const error = ref<string | undefined>(undefined)
  const touched = ref(false)

  // Valide le champ
  const validateField = (): boolean => {
    error.value = validate(value.value, rules)
    return error.value === undefined
  }

  // Marque comme touché et valide
  const touch = () => {
    touched.value = true
    validateField()
  }

  // Reset
  const reset = () => {
    value.value = initialValue
    error.value = undefined
    touched.value = false
  }

  // Computed
  const hasError = computed(() => error.value !== undefined)
  const isValid = computed(() => error.value === undefined)

  return {
    value,
    error,
    touched,
    hasError,
    isValid,
    validate: validateField,
    touch,
    reset,
  }
}
