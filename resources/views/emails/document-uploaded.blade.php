@component('mail::message')
# Nouveau Document Ajouté

Bonjour {{ $recipient_name }},

Un nouveau document a été ajouté à votre dossier.

**Dossier:** {{ $dossier_reference }}  
**Document:** {{ $document_name }}

@component('mail::button', ['url' => config('app.url') . '/dossiers'])
Voir le document
@endcomponent

Cordialement,<br>
{{ config('app.name') }}
@endcomponent
