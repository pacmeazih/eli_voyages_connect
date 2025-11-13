# 🎨 ELI-Voyages Design System

## 📐 Layout Standards

### Containers
```vue
<!-- ✅ Standard pour pages list/index -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

<!-- ✅ Pour formulaires compacts (Create/Edit) -->
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
```

### Headers
```vue
<!-- ✅ Page title (toujours text-3xl) -->
<h1 class="text-3xl font-bold text-gray-900 dark:text-white">
  Titre de la page
</h1>

<!-- ✅ Section title -->
<h2 class="text-xl font-semibold text-gray-900 dark:text-white">
  Section
</h2>
```

### Spacing
```vue
<!-- ✅ Page padding -->
<div class="py-8">

<!-- ✅ Section spacing -->
<div class="mb-6">
```

---

## 🎨 Colors

### Brand Colors (depuis tailwind.config.js)
```javascript
brand-primary    // #003040 - Teal foncé (couleur principale)
brand-secondary  // #00C0C0 - Turquoise
brand-accent     // #F0C000 - Jaune
brand-orange     // #E06000 - Orange
```

### Usage
```vue
<!-- ✅ Boutons primaires -->
<button class="bg-brand-primary hover:bg-brand-primary/90">

<!-- ✅ Liens et accents -->
<a class="text-brand-primary hover:text-brand-primary/80">

<!-- ❌ NE JAMAIS utiliser -->
bg-indigo-600, text-indigo-600, bg-blue-600, border-indigo-500
```

---

## 🧩 Components

### Buttons
```vue
<!-- ✅ Utiliser PrimaryButton component -->
<PrimaryButton @click="handleClick">
  Action
</PrimaryButton>

<!-- ❌ Éviter boutons inline -->
<button class="px-4 py-2 bg-indigo-600...">
```

### Cards
```vue
<!-- ✅ Utiliser Card component -->
<Card>
  <template #title>Titre</template>
  <template #actions>
    <button>...</button>
  </template>
  
  Contenu de la carte
</Card>

<!-- ❌ Éviter divs custom -->
<div class="bg-white rounded-lg shadow...">
```

### Forms
```vue
<!-- ✅ Input standard avec dark mode -->
<input
  type="text"
  class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 
         dark:bg-gray-700 dark:text-white
         shadow-sm focus:border-brand-primary focus:ring-brand-primary 
         sm:text-sm"
/>

<!-- ✅ Label -->
<label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
  Libellé
</label>
```

---

## 🌓 Dark Mode

### Règles obligatoires
**TOUS** les éléments doivent supporter le dark mode :

```vue
<!-- ✅ Backgrounds -->
bg-white dark:bg-gray-800
bg-gray-50 dark:bg-gray-900
bg-gray-100 dark:bg-gray-700

<!-- ✅ Texte -->
text-gray-900 dark:text-white
text-gray-600 dark:text-gray-300
text-gray-500 dark:text-gray-400

<!-- ✅ Borders -->
border-gray-200 dark:border-gray-700
border-gray-300 dark:border-gray-600

<!-- ✅ Hover states -->
hover:bg-gray-50 dark:hover:bg-gray-700
```

### Cards avec dark mode
```vue
<div class="bg-white dark:bg-gray-800 rounded-lg shadow">
  <h3 class="text-gray-900 dark:text-white">Titre</h3>
  <p class="text-gray-600 dark:text-gray-300">Description</p>
</div>
```

---

## 📊 Stats Cards Pattern

```vue
<dl class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
  <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
      Métrique
    </dt>
    <dd class="text-3xl font-semibold text-gray-900 dark:text-white">
      42
    </dd>
  </div>
</dl>
```

---

## 🎯 Status Badges

```vue
<!-- ✅ Avec brand-primary -->
<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
             bg-brand-primary/10 text-brand-primary">
  Actif
</span>

<!-- ✅ Success state -->
<span class="bg-green-100 dark:bg-green-900/20 text-green-800 dark:text-green-400">
  Complété
</span>

<!-- ✅ Warning state -->
<span class="bg-yellow-100 dark:bg-yellow-900/20 text-yellow-800 dark:text-yellow-400">
  En attente
</span>
```

---

## 📋 Tables Pattern

```vue
<div class="overflow-x-auto">
  <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
    <thead class="bg-gray-50 dark:bg-gray-800">
      <tr>
        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
          Colonne
        </th>
      </tr>
    </thead>
    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
      <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
        <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">
          Donnée
        </td>
      </tr>
    </tbody>
  </table>
</div>
```

---

## ✅ Checklist de migration d'une page

- [ ] Container à `max-w-7xl` (ou `max-w-4xl` pour formulaires)
- [ ] Header principal à `text-3xl`
- [ ] Remplacer `indigo/blue` par `brand-primary`
- [ ] Utiliser `Card` component au lieu de divs custom
- [ ] Utiliser `PrimaryButton` au lieu de boutons inline
- [ ] Ajouter `dark:` classes sur TOUS les éléments
- [ ] Vérifier focus states avec `focus:ring-brand-primary`
- [ ] Tester le dark mode toggle

---

## 🚫 Anti-patterns à éviter

```vue
<!-- ❌ Couleurs hard-coded -->
<div class="bg-indigo-600 text-blue-500">

<!-- ❌ Container inconsistent -->
<div class="max-w-6xl mx-auto">

<!-- ❌ Headers trop petits -->
<h1 class="text-2xl">

<!-- ❌ Pas de dark mode -->
<div class="bg-white text-gray-900">

<!-- ❌ Divs au lieu de components -->
<div class="bg-white rounded-lg shadow p-6">
  <!-- Utiliser <Card> à la place -->
</div>
```

---

## 📦 Components réutilisables disponibles

- `Card.vue` - Container avec title/actions slots
- `PrimaryButton.vue` - Bouton principal
- `StatusBadge.vue` - Badge de statut
- `StatCard.vue` - Carte de statistique
- `DarkModeToggle.vue` - Toggle dark mode
- `FormField.vue` - Champ de formulaire

**Toujours privilégier ces components plutôt que du code inline.**
