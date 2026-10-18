/**
 * The two password-reset flows share their pages; this maps each portal to
 * its routes and wording. Staff = `web` guard, party = applicants/respondents.
 */
export type AuthPortal = 'staff' | 'party';

export const authPortals = {
    staff: {
        accent: 'blue',
        login: 'login',
        forgot: 'password.request',
        sendCode: 'password.email',
        verifyCode: 'admin.password.otp.verify',
        resendCode: 'admin.password.otp.resend',
        resetPassword: 'password.store',
        forgotSubtitle: 'auth.forgot_password_description',
        confirmLabel: 'auth.confirm_password',
    },
    party: {
        accent: 'orange',
        login: 'applicant.login',
        forgot: 'applicant.password.request',
        sendCode: 'applicant.password.email',
        verifyCode: 'applicant.password.otp.verify',
        resendCode: 'applicant.password.otp.resend',
        resetPassword: 'applicant.password.new.update',
        forgotSubtitle: 'auth.forgot_password_subtitle',
        confirmLabel: 'auth.confirm_new_password',
    },
} as const;
