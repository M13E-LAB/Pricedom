@component('mail::message')
# Réinitialisation de votre mot de passe

Bonjour,

Vous recevez cet email car nous avons reçu une demande de réinitialisation de mot de passe pour votre compte Pricedom.

@component('mail::button', ['url' => $url])
Réinitialiser mon mot de passe
@endcomponent

Ce lien de réinitialisation expirera dans {{ config('auth.passwords.'.config('auth.defaults.passwords').'.expire') }} minutes.

Si vous n'avez pas demandé de réinitialisation de mot de passe, aucune action supplémentaire n'est requise.

Cordialement,<br>
L'équipe {{ config('app.name') }}
@endcomponent 