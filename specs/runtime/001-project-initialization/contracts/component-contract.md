# Component Contract — Bunyan Frontend (Nuxt.js + Nuxt UI)

**Version:** 1.0  
**Status:** PLANNING  
**Date:** 2026-04-10  
**Framework:** Nuxt.js 3 + Vue 3 Composition API  
**UI Library:** @nuxt/ui + Tailwind CSS v4

---

## Overview

This document defines the contract for all frontend components built in Bunyan. All components use:
- **Vue 3 Composition API** (`<script setup>` syntax)
- **TypeScript** for type safety
- **Nuxt UI** (@nuxt/ui) components for consistency
- **Tailwind CSS** logical properties for RTL support
- **i18n** for Arabic/English text

---

## Component Structure Template

### Basic Component

```vue
<script setup lang="ts">
import type { Project } from '~/types/models';

// Props with TypeScript
interface Props {
  project: Project;
  loading?: boolean;
  error?: string | null;
}

// Emits
interface Emits {
  edit: [projectId: number];
  delete: [projectId: number];
}

const props = withDefaults(defineProps<Props>(), {
  loading: false,
  error: null,
});

const emit = defineEmits<Emits>();

// i18n
const { t } = useI18n();

// Reactive state
const isOpen = ref(false);

// Methods
const handleEdit = () => {
  emit('edit', props.project.id);
};

const handleDelete = () => {
  if (confirm(t('common.confirmDelete'))) {
    emit('delete', props.project.id);
  }
};
</script>

<template>
  <UCard :ui="{ strategy: 'override' }">
    <!-- Loading state -->
    <template v-if="loading">
      <USkeleton class="h-12 w-full" />
    </template>

    <!-- Error state -->
    <template v-else-if="error">
      <UAlert
        icon="i-heroicons-exclamation-triangle"
        color="red"
        variant="soft"
        :title="t('common.error')"
        :description="error"
      />
    </template>

    <!-- Content -->
    <template v-else>
      <div class="flex items-center justify-between">
        <div>
          <h3 class="text-lg font-semibold">{{ project.title }}</h3>
          <p class="text-sm text-gray-600">{{ project.description }}</p>
        </div>
        <div class="flex gap-2">
          <UButton
            color="blue"
            variant="ghost"
            icon="i-heroicons-pencil"
            @click="handleEdit"
          >
            {{ t('common.edit') }}
          </UButton>
          <UButton
            color="red"
            variant="ghost"
            icon="i-heroicons-trash"
            @click="handleDelete"
          >
            {{ t('common.delete') }}
          </UButton>
        </div>
      </div>
    </template>
  </UCard>
</template>
```

---

## Layout Components

### Default Layout

**File:** `frontend/layouts/default.vue`

**Purpose:** Main app layout (authenticated users)

**Structure:**
```vue
<template>
  <div class="min-h-screen bg-white">
    <!-- Header -->
    <header class="border-b border-gray-100 shadow-sm">
      <div class="container mx-auto flex items-center justify-between ps-4 pe-4 py-4">
        <div class="text-2xl font-semibold">Bunyan</div>
        <nav class="flex items-center gap-4">
          <LocaleSwitcher />
          <UserMenu />
        </nav>
      </div>
    </header>

    <!-- Main content -->
    <main class="container mx-auto ps-4 pe-4 py-8">
      <slot />
    </main>

    <!-- Notifications -->
    <Notifications />
  </div>
</template>
```

**Components Included:**
- `LocaleSwitcher` — Toggle Arabic/English
- `UserMenu` — Profile, logout
- `Notifications` — Toast messages

### Auth Layout

**File:** `frontend/layouts/auth.vue`

**Purpose:** Login/register pages (unauthenticated users)

**Structure:**
```vue
<template>
  <div class="flex min-h-screen items-center justify-center bg-gray-50">
    <div class="w-full max-w-md">
      <div class="text-center mb-8">
        <h1 class="text-3xl font-bold">{{ t('common.bunyan') }}</h1>
        <p class="text-gray-600">{{ t('common.tagline') }}</p>
      </div>

      <slot />

      <div class="mt-6 text-center text-sm text-gray-600">
        <!-- Login/Register toggle -->
      </div>
    </div>
  </div>
</template>
```

---

## Form Components

### LoginForm Component

**File:** `frontend/components/Forms/LoginForm.vue`

**Props:** None  
**Emits:** `submit(credentials: LoginRequest)`

**Example:**
```vue
<script setup lang="ts">
const router = useRouter();
const { t } = useI18n();
const { $fetch } = useNuxtApp();
const authStore = useAuthStore();

const loading = ref(false);
const form = reactive({
  email: '',
  password: '',
});

const schema = z.object({
  email: z.string().email(t('validation.emailInvalid')),
  password: z.string().min(8, t('validation.passwordMinLength')),
});

const handleSubmit = async (data: any) => {
  loading.value = true;
  try {
    const response = await $fetch('/api/v1/auth/login', {
      method: 'POST',
      body: data,
    });
    
    authStore.setUser(response.data.user);
    authStore.setToken(response.data.access_token);
    
    await router.push('/dashboard');
  } catch (error) {
    // Error handled by global error middleware
  } finally {
    loading.value = false;
  }
};
</script>

<template>
  <UForm :schema="schema" @submit="handleSubmit">
    <UFormGroup :label="t('auth.email')" name="email">
      <UInput
        v-model="form.email"
        type="email"
        :placeholder="t('auth.emailPlaceholder')"
        icon="i-heroicons-envelope"
      />
    </UFormGroup>

    <UFormGroup :label="t('auth.password')" name="password">
      <UInput
        v-model="form.password"
        type="password"
        :placeholder="t('auth.passwordPlaceholder')"
        icon="i-heroicons-lock-closed"
      />
    </UFormGroup>

    <UButton type="submit" :loading="loading" block size="lg">
      {{ t('auth.login') }}
    </UButton>
  </UForm>
</template>
```

### ProjectForm Component

**File:** `frontend/components/Forms/ProjectForm.vue`

**Props:**
```typescript
interface Props {
  project?: Project | null;
  loading?: boolean;
}
```

**Emits:** `submit(data: CreateProjectRequest)`

**Structure:**
```vue
<template>
  <UForm :schema="schema" @submit="handleSubmit">
    <UFormGroup :label="t('projects.title')" name="title">
      <UInput v-model="form.title" />
    </UFormGroup>

    <UFormGroup :label="t('projects.description')" name="description">
      <UTextarea v-model="form.description" rows="4" />
    </UFormGroup>

    <UFormGroup :label="t('projects.budget')" name="budget">
      <UInput v-model.number="form.budget" type="number" />
    </UFormGroup>

    <UFormGroup :label="t('projects.contractor')" name="contractor_id">
      <USelect
        v-model="form.contractor_id"
        :options="contractors"
        option-attribute="name"
        value-attribute="id"
      />
    </UFormGroup>

    <UButton type="submit" :loading="loading">
      {{ t('common.save') }}
    </UButton>
  </UForm>
</template>
```

---

## Card Components

### ProjectCard Component

**File:** `frontend/components/Cards/ProjectCard.vue`

**Props:**
```typescript
interface Props {
  project: Project;
}
```

**Example:**
```vue
<template>
  <NuxtLink :to="`/dashboard/projects/${project.id}`">
    <UCard
      :ui="{ divide: 'divide-y divide-gray-100' }"
      class="hover:shadow-lg transition-shadow cursor-pointer"
    >
      <template #header>
        <div class="flex items-center justify-between">
          <h3 class="text-lg font-semibold">{{ project.title }}</h3>
          <UBadge
            :color="statusColor(project.status)"
            variant="subtle"
          >
            {{ t(`project.status.${project.status}`) }}
          </UBadge>
        </div>
      </template>

      <div class="space-y-2">
        <p class="text-sm text-gray-600">{{ project.description }}</p>
        <div class="flex items-center justify-between">
          <span class="text-sm text-gray-500">{{ t('projects.budget') }}</span>
          <span class="font-semibold">{{ formatCurrency(project.budget) }}</span>
        </div>
      </div>

      <template #footer>
        <div class="flex items-center gap-2 text-xs text-gray-500">
          <span>{{ formatDate(project.created_at) }}</span>
        </div>
      </template>
    </UCard>
  </NuxtLink>
</template>
```

---

## Page Components

### Dashboard Page

**File:** `frontend/pages/dashboard/index.vue`

**Structure:**
```vue
<script setup lang="ts">
definePageMeta({
  middleware: 'auth', // Require authentication
});

const { t } = useI18n();
const projectStore = useProjectStore();
const { projects, loading } = storeToRefs(projectStore);

onMounted(() => {
  projectStore.fetchProjects();
});

const roleLabel = computed(() => {
  const authStore = useAuthStore();
  return t(`user.role.${authStore.user?.role}`);
});
</script>

<template>
  <div>
    <!-- Hero -->
    <section class="mb-12">
      <h1 class="text-4xl font-bold mb-2">
        {{ t('dashboard.welcome') }}
      </h1>
      <p class="text-gray-600">{{ roleLabel }}</p>
    </section>

    <!-- Stats (if role supports) -->
    <section class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
      <UCard>
        <div class="text-center">
          <p class="text-gray-600">{{ t('dashboard.activeProjects') }}</p>
          <p class="text-3xl font-bold">{{ projects.length }}</p>
        </div>
      </UCard>
    </section>

    <!-- Projects List -->
    <section>
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-2xl font-bold">{{ t('dashboard.projects') }}</h2>
        <NuxtLink to="/dashboard/projects/create">
          <UButton icon="i-heroicons-plus">
            {{ t('projects.createNew') }}
          </UButton>
        </NuxtLink>
      </div>

      <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <USkeleton v-for="i in 6" :key="i" class="h-48" />
      </div>

      <div v-else-if="projects.length === 0" class="text-center py-12">
        <p class="text-gray-600">{{ t('projects.empty') }}</p>
      </div>

      <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <ProjectCard v-for="project in projects" :key="project.id" :project="project" />
      </div>
    </section>
  </div>
</template>
```

### Project Create Page

**File:** `frontend/pages/dashboard/projects/create.vue`

**Structure:**
```vue
<script setup lang="ts">
definePageMeta({
  middleware: 'auth',
});

const router = useRouter();
const projectStore = useProjectStore();

const handleSubmit = async (data: CreateProjectRequest) => {
  await projectStore.createProject(data);
  await router.push('/dashboard/projects');
};
</script>

<template>
  <div>
    <h1 class="text-3xl font-bold mb-8">{{ t('projects.createNew') }}</h1>
    <div class="max-w-2xl">
      <ProjectForm @submit="handleSubmit" />
    </div>
  </div>
</template>
```

---

## Composable Functions

### useAuth Composable

**File:** `frontend/composables/useAuth.ts`

```typescript
export const useAuth = () => {
  const authStore = useAuthStore();
  const router = useRouter();
  const { $fetch } = useNuxtApp();

  const login = async (email: string, password: string) => {
    const response = await $fetch('/api/v1/auth/login', {
      method: 'POST',
      body: { email, password },
    });
    
    authStore.setUser(response.data.user);
    authStore.setToken(response.data.access_token);
    await router.push('/dashboard');
  };

  const register = async (data: RegisterRequest) => {
    const response = await $fetch('/api/v1/auth/register', {
      method: 'POST',
      body: data,
    });
    
    authStore.setUser(response.data.user);
    authStore.setToken(response.data.access_token);
    await router.push('/dashboard');
  };

  const logout = async () => {
    await $fetch('/api/v1/auth/logout', { method: 'POST' });
    authStore.clearAuth();
    await router.push('/auth/login');
  };

  return {
    login,
    register,
    logout,
  };
};
```

### useProjects Composable

**File:** `frontend/composables/useProjects.ts`

```typescript
export const useProjects = () => {
  const { $fetch } = useNuxtApp();
  const projects = ref<Project[]>([]);
  const loading = ref(false);
  const error = ref<string | null>(null);

  const fetchProjects = async () => {
    loading.value = true;
    try {
      const response = await $fetch('/api/v1/projects');
      projects.value = response.data;
    } catch (e) {
      error.value = e.message;
    } finally {
      loading.value = false;
    }
  };

  const createProject = async (data: CreateProjectRequest) => {
    const response = await $fetch('/api/v1/projects', {
      method: 'POST',
      body: data,
    });
    projects.value.push(response.data);
    return response.data;
  };

  const updateProject = async (id: number, data: Partial<Project>) => {
    const response = await $fetch(`/api/v1/projects/${id}`, {
      method: 'PATCH',
      body: data,
    });
    const index = projects.value.findIndex(p => p.id === id);
    if (index !== -1) {
      projects.value[index] = response.data;
    }
    return response.data;
  };

  return {
    projects: readonly(projects),
    loading: readonly(loading),
    error: readonly(error),
    fetchProjects,
    createProject,
    updateProject,
  };
};
```

---

## i18n Integration

### Translation Files

**File:** `frontend/locales/ar.json`

```json
{
  "common": {
    "bunyan": "بنيان",
    "tagline": "منصة البناء الموحدة",
    "save": "حفظ",
    "cancel": "إلغاء",
    "delete": "حذف",
    "edit": "تعديل",
    "error": "خطأ",
    "success": "تم بنجاح",
    "loading": "جاري التحميل...",
    "empty": "لا توجد بيانات",
    "confirmDelete": "هل تريد حذف هذا العنصر؟"
  },
  "auth": {
    "login": "تسجيل الدخول",
    "register": "إنشاء حساب",
    "email": "البريد الإلكتروني",
    "emailPlaceholder": "أدخل بريدك الإلكتروني",
    "password": "كلمة المرور",
    "passwordPlaceholder": "أدخل كلمة المرور",
    "passwordConfirm": "تأكيد كلمة المرور",
    "phone": "رقم الهاتف",
    "role": "الدور"
  },
  "dashboard": {
    "welcome": "أهلا وسهلا",
    "activeProjects": "المشاريع النشطة",
    "projects": "المشاريع"
  },
  "projects": {
    "createNew": "مشروع جديد",
    "title": "عنوان المشروع",
    "description": "الوصف",
    "budget": "الميزانية",
    "contractor": "المقاول",
    "empty": "لا توجد مشاريع"
  },
  "validation": {
    "required": "هذا الحقل مطلوب",
    "emailInvalid": "البريد الإلكتروني غير صحيح",
    "passwordMinLength": "يجب أن تكون كلمة المرور 8 أحرف على الأقل"
  }
}
```

**File:** `frontend/locales/en.json`

```json
{
  "common": {
    "bunyan": "Bunyan",
    "tagline": "Unified Construction Platform",
    "save": "Save",
    "cancel": "Cancel",
    // ... (mirror structure)
  }
}
```

---

## RTL Support

### Logical Properties Example

**❌ WRONG (directional):**
```vue
<template>
  <div class="ml-4 mr-8 pl-6 pr-6">
    <!-- Will not flip in RTL -->
  </div>
</template>
```

**✅ CORRECT (logical):**
```vue
<template>
  <div class="ms-4 me-8 ps-6 pe-6">
    <!-- Will automatically flip in RTL -->
  </div>
</template>
```

### Flex/Grid Auto-flip

```vue
<template>
  <!-- Auto-flips flex direction in RTL -->
  <div class="flex items-center justify-between">
    <span>{{ t('common.label') }}</span>
    <UButton>{{ t('common.action') }}</UButton>
  </div>
</template>
```

---

## Testing Components

### Component Test Template

```typescript
// components/ProjectCard.test.ts
import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import ProjectCard from '~/components/Cards/ProjectCard.vue';

describe('ProjectCard', () => {
  it('renders project title', () => {
    const project = {
      id: 1,
      title: 'Test Project',
      description: 'Test description',
      budget: 10000,
      status: 'pending',
      created_at: '2026-04-10T12:00:00Z',
    };

    const wrapper = mount(ProjectCard, {
      props: { project },
    });

    expect(wrapper.text()).toContain('Test Project');
  });

  it('navigates to project detail on click', async () => {
    const wrapper = mount(ProjectCard, {
      props: { /* ... */ },
      global: {
        stubs: { NuxtLink: true },
      },
    });

    expect(wrapper.find('a').attributes('href')).toBe('/dashboard/projects/1');
  });

  it('displays status badge', () => {
    const wrapper = mount(ProjectCard, {
      props: { /* ... */ },
    });

    expect(wrapper.find('[class*="badge"]').exists()).toBe(true);
  });
});
```

---

## Accessibility Considerations

All components must include:

1. **ARIA Labels:** `aria-label`, `aria-describedby`
   ```vue
   <UButton aria-label="Delete project">×</UButton>
   ```

2. **Semantic HTML:** Use `<button>`, `<form>`, `<nav>` tags

3. **Keyboard Navigation:** Tab order, focus visible

4. **Color Contrast:** WCAG AA minimum

5. **RTL Text Direction:** Use `dir` attribute and logical CSS

---

**Status:** READY FOR REVIEW  
**Last Updated:** 2026-04-10
