import type { SignupPayload, SignupResponse } from '~/types/auth'

const API_ENDPOINTS = {
  SIGNUP: '/api/users',
} as const

export const signupService = (payload: SignupPayload): Promise<SignupResponse> => {
  const [firstName, ...rest] = payload.name.trim().split(' ')
  const lastName = rest.join(' ') || firstName

  return $fetch<SignupResponse>(API_ENDPOINTS.SIGNUP, {
    method: 'POST',
    headers: { 'Content-Type': 'application/ld+json' },
    body: { firstName, lastName, email: payload.email, password: payload.password },
  })
}
