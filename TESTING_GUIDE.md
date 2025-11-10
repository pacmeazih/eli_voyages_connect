# 🧪 Testing Guide - ELI VOYAGES Platform

## Overview
This guide provides step-by-step instructions to test all the newly implemented features after the major refactoring.

---

## ✅ Pre-Testing Checklist

1. **Build Assets**
   ```bash
   npm run build
   # OR for development
   npm run dev
   ```

2. **Run Migrations** (if not already done)
   ```bash
   php artisan migrate --force
   ```

3. **Clear Cache**
   ```bash
   php artisan config:clear
   php artisan route:clear
   php artisan view:clear
   php artisan cache:clear
   ```

4. **Ensure Database is Seeded**
   ```bash
   php artisan db:seed
   ```

---

## 🎨 1. Test: Vertical Sidebar Navigation

### Objective
Verify the new vertical sidebar layout works correctly for all user roles.

### Test Steps

#### A. Staff User Login (Consultant/Admin/Agent)
1. **Login** as a staff member (e.g., `admin@example.com`)
2. **Verify sidebar elements:**
   - ✅ Logo appears at top (Eli-Voyages icon)
   - ✅ User profile card with avatar
   - ✅ Navigation links visible:
     - Dashboard (home icon)
     - **Dossiers** (plural) → links to `/dossiers` (all dossiers list)
     - Documents
     - Contracts
     - Invitations (invite icon)
     - Analytics
     - Settings
   - ✅ Bottom section: Language switcher, Dark mode toggle, Logout button

3. **Test mobile responsiveness:**
   - Open browser DevTools (F12)
   - Toggle device toolbar (Ctrl+Shift+M)
   - Select mobile device (iPhone 12, Samsung Galaxy, etc.)
   - ✅ Sidebar hidden on mobile
   - ✅ Hamburger menu appears in top-left
   - ✅ Click hamburger → sidebar slides in with overlay
   - ✅ Click outside → sidebar closes

4. **Test navigation:**
   - Click each menu item
   - ✅ Active link highlighted (amber background)
   - ✅ Page content loads correctly
   - ✅ No console errors

#### B. Client User Login
1. **Login** as a client (e.g., `client@example.com` / `client123`)
2. **Verify sidebar shows CLIENT-SPECIFIC menu:**
   - ✅ Dashboard
   - ✅ **Mon dossier** (singular) → links to `/dossiers/{client_id}` (their specific dossier)
   - ✅ Documents
   - ✅ Notifications
   - ✅ Settings

3. **Click "Mon dossier":**
   - ✅ Redirects to client's specific dossier page
   - ✅ URL is `/dossiers/{id}` where ID matches their client_id
   - ✅ Shows their dossier details, documents, progress

4. **Verify client CANNOT see:**
   - ❌ "Dossiers" (plural - staff view)
   - ❌ "Invitations" menu
   - ❌ Analytics page

---

## 👥 2. Test: Client Invitation System

### Objective
Test end-to-end invitation workflow from creation to acceptance.

### Test Steps

#### A. Create Invitation (Staff Only)
1. **Login** as staff user
2. **Navigate** to `/client-invitations` or click "Invitations" in sidebar
3. **Click** "Nouvelle invitation" button (top-right, amber)
4. **Fill invitation form:**
   - Nom: `Dupont`
   - Prénom: `Marie`
   - Email: `marie.dupont@test.com` (use unique email)
   - Téléphone: `+33 6 12 34 56 78`
5. **Submit** form
6. **Verify:**
   - ✅ Success flash message: "Invitation envoyée avec succès"
   - ✅ Redirect to invitations list
   - ✅ New invitation appears in table with status "Envoyé" (blue badge)
   - ✅ Client code generated (format: **EV-2025-0001**)
   - ✅ Email sent (check logs or mailbox)

#### B. Invitation List Features
1. **Test Search:**
   - Type "Dupont" in search box
   - ✅ Results filter to show only matching invitations
   - Clear search → all invitations appear

2. **Test Status Filter:**
   - Select "Pending" → shows only pending
   - Select "Accepted" → shows only accepted
   - Select "All" → shows all

3. **Test Actions:**
   - **Resend button:**
     - Click resend icon
     - ✅ Confirm dialog appears
     - ✅ Email re-sent, `sent_at` updated
   
   - **Copy Link button:**
     - Click copy icon
     - ✅ Invitation URL copied to clipboard
     - Paste in browser → should open acceptance page
   
   - **Delete button:**
     - Click delete icon (only visible if not accepted)
     - ✅ Confirm dialog appears
     - ✅ Invitation deleted from list

#### C. Accept Invitation (Client Side)
1. **Copy invitation link** from list (or use email link)
2. **Open in incognito/private window** (to simulate client)
3. **Verify acceptance page:**
   - ✅ ELI VOYAGES logo at top
   - ✅ Client code displayed in gradient header box
   - ✅ Personal info shown (Nom, Prénom, Email, Téléphone) - read-only
   - ✅ Form fields visible:
     - Civilité (M./Mme/Mlle radio buttons)
     - Password + Confirmation
     - Optional: adresse, date_naissance, lieu_naissance, nationalite, profession
   - ✅ Terms checkbox (required)

4. **Fill form:**
   - Select civilité: `Mme`
   - Password: `password123` (min 8 chars)
   - Confirm password: `password123`
   - Optional fields: fill or leave empty
   - ✅ Check "J'accepte les conditions générales"

5. **Submit:**
   - Click "Créer mon compte"
   - ✅ Processing spinner appears
   - ✅ Account created successfully
   - ✅ Auto-login (no need to manually login)
   - ✅ Redirect to dashboard

6. **Verify account creation:**
   - ✅ User now logged in as Client
   - ✅ Sidebar shows "Mon dossier" (singular)
   - ✅ Client record created in database with client_code
   - ✅ User record created with `client_id` linked

7. **Back to staff view:**
   - Login as staff again
   - Navigate to invitations list
   - ✅ Invitation status changed to "Accepté" (green badge)
   - ✅ Accepted date shown
   - ✅ Delete button no longer visible (accepted invitations cannot be deleted)

#### D. Test Invitation Expiration
1. **Manually update invitation** in database:
   ```sql
   UPDATE client_invitations 
   SET expires_at = '2025-01-01 00:00:00' 
   WHERE email = 'test@example.com';
   ```
2. **Open invitation link**
3. **Verify:**
   - ✅ "Expired" page shown instead of acceptance form
   - ✅ Error message: "Cette invitation a expiré"
   - ✅ Contact information provided

---

## 📄 3. Test: Document Upload & Approval

### Objective
Test client document upload and staff approval/rejection workflow.

### Test Steps

#### A. Upload Document (Client Side)
1. **Login** as client user
2. **Navigate** to "Mon dossier"
3. **Find "Documents" section**
4. **Click** "Ajouter un document" button (should open modal)

   **If modal doesn't appear:**
   - Check that `DocumentUploadModal.vue` is imported in the page
   - Add trigger button manually:
   ```vue
   <button @click="showUploadModal = true">Ajouter un document</button>
   <DocumentUploadModal 
       :isOpen="showUploadModal" 
       :dossierId="dossier.id"
       @close="showUploadModal = false"
       @uploaded="handleUploaded"
   />
   ```

5. **In modal:**
   - Select document type: `Passeport`
   - Click or drag-drop file (PDF, JPG, PNG, DOC - max 10MB)
   - ✅ File preview appears with name and size
   - Add optional description: "Mon passeport valide jusqu'en 2030"
   - ✅ "Téléverser" button enabled

6. **Submit:**
   - Click "Téléverser"
   - ✅ Progress bar shows upload percentage
   - ✅ Success message appears
   - ✅ Modal closes
   - ✅ Document appears in list with status "En attente" (yellow badge)

#### B. Approve Document (Staff Side)
1. **Login** as staff user with `validate documents` permission
2. **Navigate** to dossier or documents list
3. **Find uploaded document**
4. **Verify approval actions visible:**
   - ✅ Status badge shows "En attente" (yellow)
   - ✅ Two action buttons: "Approuver" (green) and "Rejeter" (red)

5. **Test Approve:**
   - Click "Approuver" button
   - ✅ Confirmation dialog appears
   - Confirm
   - ✅ Status changes to "Approuvé" (green badge)
   - ✅ Action buttons disappear
   - ✅ Success message: "Document approuvé avec succès"
   - ✅ Activity logged in system

#### C. Reject Document (Staff Side)
1. **Upload another document** as client (repeat step A)
2. **As staff, click "Rejeter" button**
3. **Verify rejection modal:**
   - ✅ Title: "Rejeter le document"
   - ✅ Textarea for reason (required)
   - ✅ Help text: "Le client recevra cette explication par email"

4. **Fill reason:**
   - "La photo n'est pas claire. Veuillez téléverser une copie de meilleure qualité."
   
5. **Submit:**
   - ✅ Status changes to "Rejeté" (red badge)
   - ✅ Action buttons disappear
   - ✅ Info icon (ℹ) appears next to status
   - Hover/click info icon
   - ✅ Tooltip shows rejection reason

#### D. Client Views Rejection
1. **Login** as client
2. **Navigate** to "Mon dossier" → Documents tab
3. **Find rejected document:**
   - ✅ Status shows "Rejeté" (red)
   - ✅ Rejection reason visible below document
   - ✅ Client can upload a new version

---

## 📊 4. Test: Dossier Progress Tracker

### Objective
Verify visual progress tracker shows correct stages and status.

### Test Steps

#### A. Add Progress Tracker to Dossier Page
**If not already integrated:**

1. **Open** `resources/js/Pages/Dossiers/Show.vue`
2. **Import component:**
   ```vue
   import DossierProgressTracker from '@/Components/DossierProgressTracker.vue';
   ```

3. **Add to template** (near top of dossier details):
   ```vue
   <DossierProgressTracker 
       :steps="progressSteps"
       @action="handleProgressAction"
   />
   ```

4. **Define steps in script:**
   ```javascript
   const progressSteps = computed(() => [
       { 
           label: 'Soumission', 
           status: 'completed', 
           date: dossier.created_at,
           description: 'Dossier créé et soumis avec succès'
       },
       { 
           label: 'Documents', 
           status: dossier.documents_count === 0 ? 'active' : 'completed',
           description: dossier.documents_count === 0 
               ? 'Téléversez vos documents requis' 
               : `${dossier.documents_count} documents téléversés`,
           action: 'upload',
           actionLabel: 'Ajouter des documents'
       },
       { 
           label: 'Paiement', 
           status: dossier.payment_status === 'paid' ? 'completed' : 'pending',
           description: dossier.payment_status === 'paid' 
               ? 'Paiement reçu' 
               : 'En attente du paiement'
       },
       { 
           label: 'Traitement', 
           status: dossier.status === 'in_progress' ? 'active' : 'pending',
           description: 'Dossier en cours de traitement par l\'équipe'
       },
       { 
           label: 'Approbation', 
           status: dossier.status === 'completed' ? 'completed' : 'pending',
           description: 'Approbation finale et remise des documents'
       },
   ]);

   const handleProgressAction = (actionType) => {
       if (actionType === 'upload') {
           showUploadModal.value = true;
       }
   };
   ```

#### B. Verify Tracker Display
1. **Navigate** to client's "Mon dossier"
2. **Verify tracker UI:**
   - ✅ Horizontal timeline with 5 steps
   - ✅ Progress bar connects steps
   - ✅ Completed steps: green checkmark icon, filled progress bar
   - ✅ Active step: amber gradient, animated pulse
   - ✅ Pending steps: gray numbered circles

3. **Check current status card:**
   - ✅ Card below timeline with colored left border
   - ✅ Shows current active step details
   - ✅ Description text clear and informative
   - ✅ If action button defined, it appears (e.g., "Ajouter des documents")

4. **Check statistics row:**
   - ✅ Three columns: Complété (green), En cours (amber), À venir (gray)
   - ✅ Numbers match actual step counts

5. **Click action button** (if present):
   - ✅ Emits action event
   - ✅ Opens related modal (e.g., document upload modal)

#### C. Test Different Statuses
**Manually update dossier status in database to test different views:**

1. **All pending:**
   ```sql
   UPDATE dossiers SET status = 'new' WHERE id = 1;
   ```
   - ✅ Only first step (Soumission) completed

2. **Documents in progress:**
   - Upload documents
   - ✅ Documents step turns green when count > 0

3. **Payment completed:**
   ```sql
   UPDATE dossiers SET payment_status = 'paid' WHERE id = 1;
   ```
   - ✅ Payment step turns green

4. **All completed:**
   ```sql
   UPDATE dossiers SET status = 'completed' WHERE id = 1;
   ```
   - ✅ All steps green, 100% progress bar

---

## ✍️ 5. Test: Consultant Signature Order

### Objective
Verify contracts enforce consultant signs before client.

### Test Steps

#### A. Generate Contract with Signers
1. **Login** as staff
2. **Navigate** to dossier contract generation page
3. **Fill contract form:**
   - Contract type: Select type
   - Language: French
   - Variables: Fill required fields
   - **Signers:**
     - Signer 1: Type = `consultant`, Name = `Jean Dupuis`, Email = `consultant@eli-voyages.com`
     - Signer 2: Type = `client`, Name = `Marie Dupont`, Email = `client@example.com`

4. **Submit contract**
5. **Verify backend processing:**
   - Check Laravel logs: `storage/logs/laravel.log`
   - ✅ Log shows signers sorted: consultant first (order: 0), client second (order: 1)
   - ✅ Document record has `consultant_id` set

#### B. DocuSeal Signature Flow
**Prerequisites:** DocuSeal API configured in `.env`

1. **After contract submitted:**
   - ✅ DocuSeal submission created
   - ✅ Consultant receives email first
   - ✅ Client receives email AFTER consultant signs (sequential)

2. **Consultant signs:**
   - Consultant opens email link
   - Signs contract in DocuSeal
   - ✅ Webhook fires: `form.completed` or `submitter.completed`

3. **Check database after consultant signs:**
   ```sql
   SELECT consultant_signed_at, status FROM documents WHERE id = X;
   ```
   - ✅ `consultant_signed_at` has timestamp
   - ✅ Status still "pending" or "in_progress" (not fully completed yet)
   - ✅ Activity log: "Contract signed by consultant"

4. **Client signs:**
   - Client opens email link (arrives AFTER consultant signs)
   - Signs contract in DocuSeal
   - ✅ Webhook fires: `form.completed` with status = 'completed'

5. **Check database after both sign:**
   ```sql
   SELECT consultant_signed_at, completed_at, status FROM documents WHERE id = X;
   ```
   - ✅ `consultant_signed_at` has timestamp
   - ✅ `completed_at` has timestamp
   - ✅ Status = "completed"
   - ✅ Activity log: "Contract fully signed by all parties"

#### C. Test Webhook Handler
**Simulate webhook with test payload:**

1. **Send POST request** to `/api/webhooks/docuseal`:
   ```json
   {
     "event_type": "form.completed",
     "submission_id": "sub_123456",
     "submitter": {
       "role": "consultant",
       "email": "consultant@eli-voyages.com",
       "completed_at": "2025-11-10T15:30:00Z"
     },
     "status": "pending"
   }
   ```

2. **Verify:**
   - ✅ Document record updated with `consultant_signed_at`
   - ✅ Activity logged
   - ✅ No errors in logs

3. **Send completion webhook:**
   ```json
   {
     "event_type": "form.completed",
     "submission_id": "sub_123456",
     "status": "completed",
     "completed_at": "2025-11-10T16:00:00Z"
   }
   ```

4. **Verify:**
   - ✅ Document status = "completed"
   - ✅ `completed_at` timestamp set
   - ✅ Final activity logged

---

## 🔐 6. Test: Permissions & Authorization

### Objective
Ensure proper access control for different user roles.

### Test Matrix

| Feature | Client | Agent | Consultant | Admin | SuperAdmin |
|---------|--------|-------|------------|-------|------------|
| View own dossier | ✅ | ✅ | ✅ | ✅ | ✅ |
| View all dossiers | ❌ | ✅ | ✅ | ✅ | ✅ |
| Create invitations | ❌ | ❌ | ✅ | ✅ | ✅ |
| Approve documents | ❌ | ✅ | ✅ | ✅ | ✅ |
| Reject documents | ❌ | ✅ | ✅ | ✅ | ✅ |
| Generate contracts | ❌ | ❌ | ✅ | ✅ | ✅ |
| View analytics | ❌ | ❌ | ✅ | ✅ | ✅ |
| Manage settings | ❌ | ❌ | ❌ | ✅ | ✅ |

### Test Steps
1. **Login as each role**
2. **Attempt to access restricted pages**
3. **Verify:**
   - ✅ Authorized users see content
   - ✅ Unauthorized users redirected or see 403 error
   - ✅ No console errors

---

## 🌐 7. Test: Responsive Design

### Test Devices

#### Desktop (1920x1080)
- ✅ Sidebar visible, 256px width
- ✅ Content area uses remaining space
- ✅ All components render correctly

#### Tablet (768x1024)
- ✅ Sidebar still visible on large tablets
- ✅ Content adapts to smaller width
- ✅ Tables scroll horizontally if needed

#### Mobile (375x667)
- ✅ Sidebar hidden by default
- ✅ Hamburger menu visible in top-left
- ✅ Content full width
- ✅ Cards stack vertically
- ✅ Forms responsive, inputs full width
- ✅ Modals fit screen with padding

---

## 🎨 8. Test: Dark Mode

### Test Steps
1. **Click dark mode toggle** in sidebar (moon/sun icon)
2. **Verify color transitions:**
   - ✅ Background changes to dark (gray-900)
   - ✅ Text changes to light (gray-100)
   - ✅ Cards have dark background (gray-800)
   - ✅ Borders use dark colors (gray-700)
   - ✅ Amber/orange accents remain vibrant

3. **Navigate between pages:**
   - ✅ Dark mode persists across navigation
   - ✅ No flashing white backgrounds

4. **Test all components:**
   - ✅ Modals render correctly in dark mode
   - ✅ Forms readable with dark backgrounds
   - ✅ Dropdowns styled appropriately
   - ✅ Tooltips visible

---

## 🐛 Common Issues & Solutions

### Issue: Sidebar not appearing
**Solution:**
- Check that `VerticalLayout.vue` is imported correctly
- Verify `resources/js/stores/user.js` has `clientId` and `hasClientAccount`
- Check browser console for errors

### Issue: "Mon dossier" link shows 404
**Solution:**
- Ensure user has `client_id` set in database
- Run query: `UPDATE users SET client_id = 1 WHERE email = 'client@example.com';`
- Check route definition in `routes/web.php`

### Issue: Document upload modal not opening
**Solution:**
- Import `DocumentUploadModal` in parent component
- Add reactive variable: `const showUploadModal = ref(false);`
- Add modal to template with `:isOpen="showUploadModal"`

### Issue: Invitations not sending emails
**Solution:**
- Check `.env` mail configuration
- Run: `php artisan queue:work` if using queue
- Check `storage/logs/laravel.log` for mail errors
- Test with `php artisan tinker`: `Mail::to('test@example.com')->send(new TestMail());`

### Issue: Client code not generating
**Solution:**
- Check `ClientInvitation` model boot method
- Verify `generateClientCode()` function
- Check database for uniqueness conflicts
- Manually test: `ClientInvitation::create(['nom' => 'Test', ...])`

### Issue: Consultant signature order not working
**Solution:**
- Verify DocuSeal API supports `order` parameter
- Check webhook logs in `storage/logs/laravel.log`
- Test webhook handler with manual POST request
- Ensure `consultant_id` column exists in `documents` table

---

## ✅ Final Validation Checklist

- [ ] All pages load without errors
- [ ] Sidebar navigation works for all roles
- [ ] Client invitation flow complete (create → send → accept)
- [ ] Client codes generate in correct format (EV-YYYY-XXXX)
- [ ] Document upload functional
- [ ] Document approval/rejection works
- [ ] Progress tracker displays correctly
- [ ] Consultant signs before client in contracts
- [ ] Mobile responsive design working
- [ ] Dark mode functional
- [ ] Permissions enforced correctly
- [ ] No console errors in browser
- [ ] No errors in Laravel logs
- [ ] Assets compiled successfully (`npm run build`)

---

## 🚀 Next Steps After Testing

1. **Fix any issues** found during testing
2. **Add toast notifications** for better UX feedback
3. **Implement email notifications** for document approvals/rejections
4. **Add file preview** in document upload modal
5. **Create admin panel** for client code management
6. **Add search/filter** in documents list
7. **Implement pagination** in progress tracker for many steps
8. **Add export to PDF** for contracts and documents

---

**Happy Testing! 🎉**

For issues or questions, contact the development team.
