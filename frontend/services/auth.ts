import type { SignupPayload, SignupResponse } from '~/types/auth'

const API_ENDPOINTS = {
  SIGNUP: '/api/register',
} as const

export const signupService = (payload: SignupPayload): Promise<SignupResponse> => {
  return $fetch<SignupResponse>(API_ENDPOINTS.SIGNUP, {
    method: 'POST',
    body: payload,
  })
}
