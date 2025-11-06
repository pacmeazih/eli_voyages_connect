@component('mail::message')
# You're Invited!

Hello,

You have been invited to join **{{ config('app.name') }}** as a **{{ $invitation->role }}**.

{{ $invitation->inviter->name }} has invited you to collaborate on our platform.

@component('mail::button', ['url' => $acceptUrl])
Accept Invitation
@endcomponent

This invitation will expire on **{{ $expiresAt }}**.

If you have any questions, please contact your administrator.

Thanks,<br>
{{ config('app.name') }}

---

<small>If you did not expect to receive this invitation, you can safely ignore this email.</small>
@endcomponent
