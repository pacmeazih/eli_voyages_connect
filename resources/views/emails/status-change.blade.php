@component('mail::message')
# Mise à Jour du Statut

Bonjour {{ $recipient_name }},

Le statut de votre dossier **{{ $dossier_reference }}** a été mis à jour.

**Ancien statut:** {{ $old_status }}  
**Nouveau statut:** {{ $new_status }}

@component('mail::button', ['url' => config('app.url') . '/dossiers'])
Voir les détails
@endcomponent

Cordialement,<br>
{{ config('app.name') }}
@endcomponent
