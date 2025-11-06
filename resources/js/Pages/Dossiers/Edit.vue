<template>
    <AppLayout>
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-900">Éditer Dossier {{ dossier.reference }}</h1>
                <p class="text-sm text-gray-600 mt-1">Mettre à jour le client, le titre ou les notes.</p>
            </div>
            <Card>
                <DossierForm
                    :clients="clients"
                    :initial-values="{ client_id: dossier.client_id, title: dossier.title, notes: dossier.notes }"
                    :submit-route="route('dossiers.update', dossier.id)"
                    method="put"
                    submit-label="Mettre à jour"
                />
            </Card>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Card from '@/Components/Card.vue';
import DossierForm from './Partials/DossierForm.vue';

const props = defineProps({
    dossier: { type: Object, required: true },
    clients: { type: Array, required: true },
});
</script>
