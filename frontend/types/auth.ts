export interface SignupPayload {
  name: string
  email: string
  password: string
}

export interface AuthUser {
  id: string
  email: string
  name: string
}

export interface SignupResponse {
  token: string
  user: AuthUser
}
