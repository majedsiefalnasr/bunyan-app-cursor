<script setup lang="ts">
    const { locale, locales, setLocale } = useI18n();
    const { setDirection } = useDirection();

    const availableLocales = computed(() =>
        locales.value.map((l) => {
            const code = typeof l === 'string' ? l : l.code;
            return {
                label: typeof l === 'string' ? l : (l.name ?? l.code),
                /** Stable E2E hook; avoids role-based queries that differ across runners. */
                class: `e2e-locale-option e2e-locale-${code}`,
                click: async () => {
                    await setLocale(code);
                    const dir = typeof l !== 'string' && l.dir ? l.dir : 'ltr';
                    setDirection(dir as 'rtl' | 'ltr');
                },
            };
        })
    );

    const currentLocaleName = computed(() => {
        const found = locales.value.find((l) =>
            typeof l === 'string' ? l === locale.value : l.code === locale.value
        );
        if (!found) return locale.value;
        return typeof found === 'string' ? found : (found.name ?? found.code);
    });
</script>

<template>
    <UDropdown :items="[availableLocales]">
        <!-- as="div": avoid <button> inside Headless UI MenuButton (div[role=button]); nested controls break menus on Linux CI. -->
        <UButton
            as="div"
            color="gray"
            variant="ghost"
            :aria-label="$t('shell.nav.main')"
            icon="i-heroicons-language"
            trailing-icon="i-heroicons-chevron-down"
            size="sm"
            data-testid="language-switcher"
        >
            {{ currentLocaleName }}
        </UButton>
    </UDropdown>
</template>
