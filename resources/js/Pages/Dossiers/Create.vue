<template>
    <VerticalLayout>
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-900">Nouveau Dossier</h1>
                <p class="text-sm text-gray-600 mt-1">Créer un dossier client avec titre et notes.</p>
            </div>
            <Card>
                <DossierForm
                    :clients="clients"
                    :submit-route="route('dossiers.store')"
                    method="post"
                    submit-label="Créer le dossier"
                />
            </Card>
        </div>
    </VerticalLayout>
</template>

<script setup>
import VerticalLayout from '@/Layouts/VerticalLayout.vue';
import Card from '@/Components/Card.vue';
import DossierForm from './Partials/DossierForm.vue';

const props = defineProps({
    clients: { type: Array, required: true },
});
</script>
